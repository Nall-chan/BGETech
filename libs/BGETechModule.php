<?php

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.0
 *
 */
require_once(__DIR__ . "/BGETechTraits.php");  // diverse Klassen

/**
 * BGETech ist die Basisklasse für alle Energie-Zähler der Firma B+G E-Tech
 * Erweitert ipsmodule
 */
class BGETech extends IPSModule
{

    use Semaphore,
        VariableProfile;
    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->ConnectParent("{A5F663AB-C400-4FE5-B207-4D67CC030564}");
        $this->RegisterPropertyInteger("Interval", 0);
        $this->RegisterTimer("UpdateTimer", 0, static::PREFIX . '_RequestRead($_IPS["TARGET"]);');
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->RegisterProfileFloat('VaR', '', '', ' VAr', 0, 0, 0, 2);
        $this->RegisterProfileFloat('VA', '', '', ' VA', 0, 0, 0, 2);
        $this->RegisterProfileFloat('PhaseAngle', '', '', ' °', 0, 0, 0, 2);
        $this->RegisterProfileFloat('Intensity.F', '', '', ' %', 0, 100, 0, 2);
        $this->RegisterProfileFloat('kVArh', '', '', ' kVArh', 0, 100, 0, 2);
        foreach (static::$Variables as $i => $Variable) {
            $this->MaintainVariable(str_replace(' ', '', $Variable[0]), $this->Translate($Variable[0]), $Variable[1], $Variable[2], $i + 1, true);
        }
        if ($this->ReadPropertyInteger("Interval") > 0) {
            $this->SetTimerInterval("UpdateTimer", $this->ReadPropertyInteger("Interval"));
        } else {
            $this->SetTimerInterval("UpdateTimer", 0);
        }
    }

    /**
     * IPS-Instanz Funktion PREFIX_RequestRead.
     * Ließt alle Werte aus dem Gerät.
     *
     * @access public
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

    private function ReadData()
    {

        foreach (static::$Variables as $Variable) {
            $ReadValue = $this->SendDataToParent(json_encode(array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => $Variable[4], "Address" => $Variable[3], "Quantity" => $Variable[5], "Data" => "")));
            if ($ReadValue === false) {
                return false;
            }
            $Value = unpack("f", strrev(substr($ReadValue, 2)))[1];
            $this->SendDebug($Variable[0], $Value, 0);
            SetValue($this->GetIDForIdent(str_replace(' ', '', $Variable[0])), $Value);
        }
        return true;
    }

}
