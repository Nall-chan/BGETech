<?php

declare(strict_types=1);

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2022 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       4.00
 *
 */
require_once __DIR__ . '/SemaphoreHelper.php';  // diverse Klassen
eval('declare(strict_types=1);namespace BGETech {?>' . file_get_contents(__DIR__ . '/helper/VariableHelper.php') . '}');
eval('declare(strict_types=1);namespace BGETech {?>' . file_get_contents(__DIR__ . '/helper/VariableProfileHelper.php') . '}');

/**
 * BGETech ist die Basisklasse für alle Energie-Zähler der Firma B+G E-Tech
 * Erweitert ipsmodule.
 * @property array $Variables
 * @method void RegisterProfileInteger(string $Name, string $Icon, string $Prefix, string $Suffix, int $MinValue, int $MaxValue, float $StepSize)
 * @method void RegisterProfileFloat(string $Name, string $Icon, string $Prefix, string $Suffix, float $MinValue, float $MaxValue, float $StepSize, int $Digits)
 */
class BGETech extends IPSModuleStrict
{
    use \BGETech\SemaphoreHelper;
    use \BGETech\VariableHelper;
    use \BGETech\VariableProfileHelper;
    public const Swap = true;
    public const PREFIX = '';
    /** ModBus Gateway von Symcon */
    public const ModBusGateway = '{A5F663AB-C400-4FE5-B207-4D67CC030564}';
    /** Wartezeit nach der Abfrage eines Zählers in µs, bevor der Bus für den nächsten frei ist */
    public const BusDeadTime = 333000;
    /** Spalten der Variablenliste, die aus der Registertabelle im Code kommen (nicht vom Nutzer) */
    private const RegisterColumns = ['VarType', 'Profile', 'Address', 'Function', 'Quantity'];

    /**
     * Interne Funktion des SDK.
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyInteger('Interval', 0);
        $Variables = [];
        foreach (static::$Variables as $Pos => $Variable) {
            $Variables[] = $this->NewVariableRow($Variable, $Pos + 1);
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
        $this->RegisterTimer('UpdateTimer', 0, static::PREFIX . '_RequestRead($_IPS["TARGET"]);');
    }

    /**
     * Interne Funktion des SDK.
     * Übernimmt Korrekturen der Registertabelle in bestehende Instanzen und ergänzt neue Register.
     * Name, Position und Aktiv-Status der Zeilen bleiben unverändert.
     */
    public function Migrate(string $JSONData): string
    {
        // Als Objekt dekodieren, damit leere Objekte (z.B. attributes) beim Zurückschreiben Objekte bleiben.
        $Data = json_decode($JSONData);
        if (!isset($Data->configuration->Variables)) {
            return '';
        }
        $Rows = json_decode($Data->configuration->Variables, true);
        if (!is_array($Rows)) {
            return '';
        }
        $Migrated = $this->MigrateVariableRows($Rows);
        if ($Migrated === $Rows) {
            return '';
        }
        $Data->configuration->Variables = json_encode($Migrated);
        return json_encode($Data);
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        $this->RegisterProfileFloat('VaR', '', '', ' VAr', 0, 0, 0, 2);
        $this->RegisterProfileFloat('VA', '', '', ' VA', 0, 0, 0, 2);
        $this->RegisterProfileFloat('PhaseAngle', '', '', ' °', 0, 0, 0, 2);
        $this->RegisterProfileFloat('Intensity.F', '', '', ' %', 0, 100, 0, 2);
        $this->RegisterProfileFloat('kVArh', '', '', ' kVArh', 0, 100, 0, 2);
        $this->RegisterProfileFloat('kVAh', '', '', ' kVAh', 0, 0, 0, 2);
        $this->RegisterProfileFloat('Ah', '', '', ' Ah', 0, 0, 0, 2);
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
        if ($this->ReadPropertyInteger('Interval') < 500) {
            if ($this->ReadPropertyInteger('Interval') != 0) {
                $this->SetStatus(IS_EBASE + 1);
            } else {
                $this->SetStatus(IS_ACTIVE);
            }
            $this->SetTimerInterval('UpdateTimer', 0);
        } else {
            $this->SetTimerInterval('UpdateTimer', $this->ReadPropertyInteger('Interval'));
            $this->SetStatus(IS_ACTIVE);
        }
    }

    /**
     * Interne Funktion des SDK.
     * Die Zähler werden an ein ModBus Gateway angeschlossen, auch an ein bereits vorhandenes.
     */
    public function GetCompatibleParents(): string
    {
        return json_encode([
            'type'      => 'connect',
            'moduleIDs' => [self::ModBusGateway]
        ]);
    }

    /**
     * IPS-Instanz Funktion PREFIX_RequestRead.
     * Ließt alle Werte aus dem Gerät.
     *
     * @return bool True wenn Befehl erfolgreich ausgeführt wurde, sonst false.
     */
    public function RequestRead(): bool
    {
        $Gateway = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($Gateway == 0) {
            trigger_error($this->Translate('Instance has no active parent.'), E_USER_NOTICE);
            return false;
        }
        $IO = IPS_GetInstance($Gateway)['ConnectionID'];
        if ($IO == 0) {
            trigger_error($this->Translate('Instance has no active parent.'), E_USER_NOTICE);
            return false;
        }
        if (!$this->lock($IO)) {
            return false;
        }
        $Result = $this->ReadData();
        usleep(self::BusDeadTime);
        $this->unlock($IO);
        return $Result;
    }

    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Form['actions'][0]['onClick'] = static::PREFIX . '_RequestRead($id);';
        if (count(static::$Variables) == 1) {
            unset($Form['elements'][1]);
        }
        return json_encode($Form);
    }

    /**
     * Setzte eine IPS-Variable auf den Wert von $value.
     *
     * @param array $Variable Statusvariable
     * @param mixed $Value    Neuer Wert der Statusvariable.
     */
    protected function SetValueExt(array $Variable, mixed $Value): bool
    {
        $id = $this->FindIDForIdent($Variable['Ident']);
        if (!$id) {
            $this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $Variable['Keep']);
        }
        $this->SetValue($Variable['Ident'], $Value);
        return true;
    }

    /**
     * Erzeugt eine Zeile der Variablenliste aus einer Zeile der Registertabelle.
     *
     * @param array $Variable Zeile aus static::$Variables.
     * @param int $Pos Position der Variable.
     *
     * @return array Zeile der Property Variables.
     */
    private function NewVariableRow(array $Variable, int $Pos): array
    {
        return [
            'Ident'    => str_replace(' ', '', $Variable[0]),
            'Name'     => $this->Translate($Variable[0]),
            'VarType'  => $Variable[1],
            'Profile'  => $Variable[2],
            'Address'  => $Variable[3],
            'Function' => $Variable[4],
            'Quantity' => $Variable[5],
            'Pos'      => $Pos,
            'Keep'     => $Variable[6]
        ];
    }

    /**
     * Gleicht die gespeicherte Variablenliste mit der Registertabelle im Code ab.
     *
     * - Register-Spalten (Typ, Profil, Adresse, Funktion, Anzahl) werden aus dem Code übernommen.
     * - Zeilen, deren Ident bereits vergeben ist (frühere doppelte Idents), werden über Adresse und Funktion
     *   dem passenden Register zugeordnet und erhalten dessen Ident und Namen.
     * - Neue Register werden am Ende ergänzt.
     *
     * @param array $Rows Gespeicherte Variablenliste.
     *
     * @return array Neue Variablenliste.
     */
    private function MigrateVariableRows(array $Rows): array
    {
        $ByIdent = [];
        $ByAddress = [];
        foreach (static::$Variables as $Variable) {
            $Ident = str_replace(' ', '', $Variable[0]);
            $ByIdent[$Ident] = $Variable;
            $ByAddress[$Variable[4] . ':' . $Variable[3]] = $Ident;
        }
        $Used = [];
        $MaxPos = 0;
        foreach ($Rows as &$Row) {
            $MaxPos = max($MaxPos, (int) $Row['Pos']);
            $Ident = $Row['Ident'];
            $Definition = $ByIdent[$Ident] ?? null;
            if (($Definition === null) || isset($Used[$Ident]) || ($Definition[3] != $Row['Address'])) {
                // Ident unbekannt, doppelt oder zu einem anderen Register gehörend: über die Adresse zuordnen
                $Key = $Row['Function'] . ':' . $Row['Address'];
                if (isset($ByAddress[$Key]) && !isset($Used[$ByAddress[$Key]]) && !$this->IdentInRows($Rows, $ByAddress[$Key], $Row)) {
                    $Ident = $ByAddress[$Key];
                    $Definition = $ByIdent[$Ident];
                    $Row['Ident'] = $Ident;
                    $Row['Name'] = $this->Translate($Definition[0]);
                } elseif (($Definition === null) || isset($Used[$Ident])) {
                    // Kein passendes Register (z.B. entfernt): Zeile unverändert lassen
                    continue;
                }
            }
            $Used[$Ident] = true;
            $Row['VarType'] = $Definition[1];
            $Row['Profile'] = $Definition[2];
            $Row['Address'] = $Definition[3];
            $Row['Function'] = $Definition[4];
            $Row['Quantity'] = $Definition[5];
        }
        unset($Row);
        foreach ($ByIdent as $Ident => $Definition) {
            if (!isset($Used[$Ident]) && !$this->IdentInRows($Rows, $Ident)) {
                $Rows[] = $this->NewVariableRow($Definition, ++$MaxPos);
            }
        }
        return $Rows;
    }

    /**
     * Prüft, ob ein Ident in der Variablenliste vorkommt.
     *
     * @param array $Rows Variablenliste.
     * @param string $Ident Gesuchter Ident.
     * @param array|null $Except Diese Zeile nicht berücksichtigen.
     *
     * @return bool True, wenn der Ident vorkommt.
     */
    private function IdentInRows(array $Rows, string $Ident, ?array $Except = null): bool
    {
        foreach ($Rows as $Row) {
            if (($Row['Ident'] == $Ident) && ($Row !== $Except)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Liest alle aktiven Register.
     * Ein Register, das der Zähler nicht kennt (ModBus Exception), wird übersprungen.
     * Antwortet der Zähler nicht, wird die Abfrage abgebrochen.
     *
     * @return bool True, wenn der Zähler geantwortet hat.
     */
    private function ReadData(): bool
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
            $Error = '';
            set_error_handler(function (int $errno, string $errstr) use (&$Error): bool
            {
                $Error = $errstr;
                return true;
            });
            try {
                $ReadData = $this->SendDataToParent(json_encode($SendData));
            } finally {
                restore_error_handler();
            }
            if (($ReadData === false) || ($ReadData === '')) {
                $this->SendDebug('ERROR ' . $Variable['Name'], $Error, 0);
                if ($this->IsModBusException($Error)) {
                    // Register unbekannt oder nicht lesbar: nächstes Register
                    continue;
                }
                trigger_error($Error, E_USER_NOTICE);
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

    /**
     * Prüft, ob eine Fehlermeldung des ModBus Gateway eine Exception des Zählers ist (z.B. ILLEGAL_DATA_ADDRESS).
     * Eine Zeitüberschreitung wird als übersetzter Satz gemeldet und ist keine Exception.
     *
     * @param string $Error Fehlermeldung.
     *
     * @return bool True bei einer ModBus Exception.
     */
    private function IsModBusException(string $Error): bool
    {
        return preg_match('/^[A-Z][A-Z_]+$/', $Error) === 1;
    }

    /**
     * Wandelt die Rohdaten eines Registers in den Wert der Variable.
     *
     * Mit Swap (Standard) wurden die Bytes vorher umgedreht und liegen Little-Endian vor.
     * Ohne Swap (DRT710M) bleibt die bisherige Auswertung unverändert, da sie ohne Gerät nicht geprüft werden kann.
     *
     * @param array $Variable Zeile der Variablenliste.
     * @param string $Value Rohdaten.
     *
     * @return mixed Wert oder null, wenn Typ und Länge nicht passen.
     */
    private function ConvertValue(array $Variable, string $Value): mixed
    {
        if (!static::Swap) {
            return $this->ConvertValueUnswapped($Variable, $Value);
        }
        switch ($Variable['VarType']) {
            case VARIABLETYPE_BOOLEAN:
                return strlen($Value) > 0 ? ord($Value) == 0x01 : null;
            case VARIABLETYPE_INTEGER:
                switch (strlen($Value)) {
                    case 2:
                        return unpack('v', $Value)[1];
                    case 4:
                        return unpack('V', $Value)[1];
                    case 8:
                        return unpack('P', $Value)[1];
                }
                break;
            case VARIABLETYPE_FLOAT:
                switch (strlen($Value)) {
                    case 4:
                        return unpack('g', $Value)[1];
                    case 8:
                        return unpack('e', $Value)[1];
                }
                break;
            case VARIABLETYPE_STRING:
                return $Value;
        }
        return null;
    }

    /**
     * Bisherige Auswertung für Zähler ohne Swap (DRT710M), unverändert übernommen.
     * Annahme (ungeprüft): Bei 2 Registern liegt das niederwertige Wort zuerst, daher wird nur das erste Wort gelesen.
     *
     * @param array $Variable Zeile der Variablenliste.
     * @param string $Value Rohdaten.
     *
     * @return mixed Wert oder null, wenn Typ und Länge nicht passen.
     */
    private function ConvertValueUnswapped(array $Variable, string $Value): mixed
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
                if (in_array($Variable['Quantity'], [2, 4, 8])) {
                    return unpack('f', $Value)[1];
                }
                break;
            case VARIABLETYPE_STRING:
                return $Value;
        }
        return null;
    }
}
