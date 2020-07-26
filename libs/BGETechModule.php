<?php

declare(strict_types=1);

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2013 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 *
 */
require_once __DIR__ . '/SemaphoreHelper.php';  // diverse Klassen
eval('declare(strict_types=1);namespace BGETech {?>' . file_get_contents(__DIR__ . '/helper/VariableProfileHelper.php') . '}');

/**
 * BGETech ist die Basisklasse für alle Energie-Zähler der Firma B+G E-Tech
 * Erweitert ipsmodule.
 * @property array $Variables
 */
class BGETech extends IPSModule
{
    use \BGETech\SemaphoreHelper,
        \BGETech\VariableProfileHelper;
    const Swap = true;

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->ConnectParent('{A5F663AB-C400-4FE5-B207-4D67CC030564}');
        $this->RegisterPropertyInteger('Interval', 0);
        $Variables = [];
        foreach (static::$Variables as $Pos => $Variable) {
            $Variables[] = [
                'Ident'    => str_replace(' ', '', $Variable[0]),
                'Name'     => $this->Translate($Variable[0]),
                'VarType'  => $Variable[1],
                'Profile'  => $Variable[2],
                'Address'  => $Variable[3],
                'Function' => $Variable[4],
                'Quantity' => $Variable[5],
                'Pos'      => $Pos + 1,
                'Keep'     => $Variable[6]
            ];
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
        $this->RegisterTimer('UpdateTimer', 0, static::PREFIX . '_RequestRead($_IPS["TARGET"]);');
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->RegisterProfileFloat('VaR', '', '', ' VAr', 0, 0, 0, 2);
        $this->RegisterProfileFloat('VA', '', '', ' VA', 0, 0, 0, 2);
        $this->RegisterProfileFloat('PhaseAngle', '', '', ' °', 0, 0, 0, 2);
        $this->RegisterProfileFloat('Intensity.F', '', '', ' %', 0, 100, 0, 2);
        $this->RegisterProfileFloat('kVArh', '', '', ' kVArh', 0, 100, 0, 2);
        $this->RegisterProfileInteger('Volt.I', 'Electricity', '', ' V', 0, 0, 0);
        $this->RegisterProfileInteger('Hertz.I', 'Electricity', '', ' Hz', 0, 0, 0);
        $this->RegisterProfileInteger('Ampere.I', 'Electricity', '', ' A', 0, 0, 0);
        $this->RegisterProfileInteger('Watt.I', 'Electricity', '', ' W', 0, 0, 0);
        $this->RegisterProfileInteger('VaR.I', '', '', ' VAr', 0, 0, 0);
        $this->RegisterProfileInteger('VA.I', '', '', ' VA', 0, 0, 0);
        $this->RegisterProfileInteger('Electricity.I', '', '', ' kWh', 0, 0, 0);

        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            @$this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $Variable['Keep']);
        }
        if ($this->ReadPropertyInteger('Interval') > 0) {
            $this->SetTimerInterval('UpdateTimer', $this->ReadPropertyInteger('Interval'));
        } else {
            $this->SetTimerInterval('UpdateTimer', 0);
        }
    }

    /**
     * IPS-Instanz Funktion PREFIX_RequestRead.
     * Ließt alle Werte aus dem Gerät.
     *
     * @return bool True wenn Befehl erfolgreich ausgeführt wurde, sonst false.
     */
    public function RequestRead()
    {
        $Gateway = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($Gateway == 0) {
            return false;
        }
        $IO = IPS_GetInstance($Gateway)['ConnectionID'];
        if ($IO == 0) {
            return false;
        }
        if (!$this->lock($IO)) {
            return false;
        }
        $Result = $this->ReadData();
        IPS_Sleep(333);
        $this->unlock($IO);
        return $Result;
    }

    protected function ModulErrorHandler($errno, $errstr)
    {
        $this->SendDebug('ERROR', utf8_decode($errstr), 0);
        echo $errstr;
    }

    private function ReadData()
    {
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            if (!$Variable['Keep']) {
                continue;
            }
            $SendData['DataID'] = '{E310B701-4AE7-458E-B618-EC13A1A6F6A8}';
            $SendData['Function'] = $Variable['Function'];
            $SendData['Address'] = $Variable['Address'];
            $SendData['Quantity'] = $Variable['Quantity'];
            $SendData['Data'] = '';
            set_error_handler([$this, 'ModulErrorHandler']);
            $ReadData = $this->SendDataToParent(json_encode($SendData));
            restore_error_handler();
            if ($ReadData === false) {
                return false;
            }
            $ReadValue = substr($ReadData, 2);
            $this->SendDebug($Variable['Name'] . ' RAW', $ReadValue, 1);
            if (static::Swap) {
                $ReadValue = strrev($ReadValue);
            }
            $Value = $this->ConvertValue($Variable, $ReadValue);
            if ($Value === null) {
                $this->LogMessage(sprintf($this->Translate('Combination of type and size of value (%s) not supported.'), $Variable['Name']), KL_ERROR);
                continue;
            }
            $this->SendDebug($Variable['Name'], $Value, 0);
            $this->SetValueExt($Variable, $Value);
        }
        return true;
    }

    private function ConvertValue(array $Variable, string $Value)
    {
        switch ($Variable['VarType']) {
            case VARIABLETYPE_BOOLEAN:
                if ($Variable['Quantity'] == 1) {
                    return ord($Value) == 0x01;
                }
                break;
            case VARIABLETYPE_INTEGER:
                switch ($Variable['Quantity']) {
                    case 1:
                        return ord($Value);
                    case 2:
                        return unpack('n', $Value)[1];
                    case 4:
                        return unpack('N', $Value)[1];
                    case 8:
                        return unpack('J', $Value)[1];
                }
                break;
            case VARIABLETYPE_FLOAT:
                switch ($Variable['Quantity']) {
                    case 2:
                        return unpack('f', $Value)[1];
                    case 4:
                        return unpack('f', $Value)[1];
                    case 8:
                        return unpack('f', $Value)[1];
                }
                break;
            case VARIABLETYPE_STRING:
                return $Value;
        }
        return null;
    }

    /**
     * Setzte eine IPS-Variableauf den Wert von $value.
     *
     * @param array $Variable Statusvariable
     * @param mixed $Value    Neuer Wert der Statusvariable.
     */
    protected function SetValueExt($Variable, $Value)
    {
        $id = @$this->GetIDForIdent($Variable['Ident']);
        if ($id == false) {
            $this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $Variable['Keep']);
        }
        if (method_exists('IPSModule', 'SetValue')) {
            parent::SetValue($Variable['Ident'], $Value);
        } else {
            $id = @$this->GetIDForIdent($Variable['Ident']);
            SetValueFloat($id, $Value);
        }
        return true;
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Form['actions'][0]['onClick'] = static::PREFIX . '_RequestRead($id);';
        if (count(static::$Variables) == 1) {
            unset($Form['elements'][1]);
        }
        //$this->SendDebug('form', json_encode($Form), 0);
        return json_encode($Form);
    }
}
