<?

class SDM630 extends IPSModule
{

    public function __construct($InstanceID)
    {
        parent::__construct($InstanceID);
    }

    public function Create()
    {
        parent::Create();

        $this->ConnectParent("{A5F663AB-C400-4FE5-B207-4D67CC030564}");

        $this->RegisterPropertyInteger("Interval", 0);

        $this->RegisterTimer("UpdateTimer", 0, "SDM630_RequestRead(\$_IPS['TARGET']);");
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->RegisterVariableFloat("VoltL1", "Volt L1", "Volt.230", 1);
        $this->RegisterVariableFloat("VoltL2", "Volt L2", "Volt.230", 1);
        $this->RegisterVariableFloat("VoltL3", "Volt L3", "Volt.230", 1);

        $this->RegisterVariableFloat("AmpereL1", "Ampere L1", "Ampere.16", 2);
        $this->RegisterVariableFloat("AmpereL2", "Ampere L2", "Ampere.16", 2);
        $this->RegisterVariableFloat("AmpereL3", "Ampere L3", "Ampere.16", 2);

        $this->RegisterVariableFloat("WattL1", "Watt L1", "Watt.14490", 4);
        $this->RegisterVariableFloat("WattL2", "Watt L2", "Watt.14490", 4);
        $this->RegisterVariableFloat("WattL3", "Watt L3", "Watt.14490", 4);

        $this->RegisterVariableFloat("VArL1", "VAr L1", "", 5);
        $this->RegisterVariableFloat("VArL2", "VAr L2", "", 5);
        $this->RegisterVariableFloat("VArL3", "VAr L3", "", 5);

        $this->RegisterVariableFloat("VAL1", "VA L1", "", 6);
        $this->RegisterVariableFloat("VAL2", "VA L2", "", 6);
        $this->RegisterVariableFloat("VAL3", "VA L3", "", 6);

        $this->RegisterVariableFloat("PhaseAngleL1", "Phase angle L1", "", 7);
        $this->RegisterVariableFloat("PhaseAngleL2", "Phase angle L2", "", 7);
        $this->RegisterVariableFloat("PhaseAngleL3", "Phase angle L3", "", 7);

        $this->RegisterVariableFloat("Frequenz", "Frequenz", "Hertz.50", 3);
        $this->RegisterVariableFloat("TotalL1", "Total L1 kWh", "Electricity", 8);
        $this->RegisterVariableFloat("TotalL2", "Total L2 kWh", "Electricity", 8);
        $this->RegisterVariableFloat("TotalL3", "Total L3 kWh", "Electricity", 8);

        if ($this->ReadPropertyInteger("Interval") > 0)
            $this->SetTimerInterval("UpdateTimer", $this->ReadPropertyInteger("Interval"));
        else
            $this->SetTimerInterval("UpdateTimer", 0);
    }

    public function RequestRead()
    {

        $Gateway = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($Gateway == 0)
            return false;
        $IO = IPS_GetInstance($Gateway)['ConnectionID'];
        if ($IO == 0)
            return false;
        if (!$this->lock($IO))
            return false;

        for ($index = 0; $index < 3; $index++)
        {
            $Volt = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 4, "Address" => ($index * 2), "Quantity" => 2, "Data" => "")));
            if ($Volt === false)
            {
                $this->unlock($IO);
                return false;
            }
            $Volt = unpack("G", substr($Volt, 2))[1];
            $this->SendDebug('Volt L' . ($index + 1), $Volt, 0);
            SetValue($this->GetIDForIdent("VoltL" . ($index + 1)), $Volt);
        }

        for ($index = 0; $index < 3; $index++)
        {
            $Ampere = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 4, "Address" => 6 + ($index * 2), "Quantity" => 2, "Data" => "")));
            if ($Ampere === false)
            {
                $this->unlock($IO);
                return false;
            }
            $Ampere = unpack("G", substr($Ampere, 2))[1];
            $this->SendDebug('Ampere L' . ($index + 1), $Ampere, 0);
            SetValue($this->GetIDForIdent("AmpereL" . ($index + 1)), $Ampere);
        }

        for ($index = 0; $index < 3; $index++)
        {
            $Watt = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 4, "Address" => 12 + ($index * 2), "Quantity" => 2, "Data" => "")));
            if ($Watt === false)
            {
                $this->unlock($IO);
                return false;
            }
            $Watt = unpack("G", substr($Watt, 2))[1];
            $this->SendDebug('Watt L' . ($index + 1), $Watt, 0);
            SetValue($this->GetIDForIdent("WattL" . ($index + 1)), $Watt);
        }

        //Scheinleistung
        for ($index = 0; $index < 3; $index++)
        {
            $Va = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 4, "Address" => 18 + ($index * 2), "Quantity" => 2, "Data" => "")));
            if ($Va === false)
            {
                $this->unlock($IO);
                return false;
            }
            $Va = unpack("G", substr($Va, 2))[1];
            $this->SendDebug('VA L' . ($index + 1), $Va, 0);
            SetValue($this->GetIDForIdent("VAL" . ($index + 1)), $Va);
        }

        //Blindleistung
        for ($index = 0; $index < 3; $index++)
        {
            $Var = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 4, "Address" => 24 + ($index * 2), "Quantity" => 2, "Data" => "")));
            if ($Var === false)
            {
                $this->unlock($IO);
                return false;
            }
            $Var = unpack("G", substr($Var, 2))[1];
            $this->SendDebug('VAr L' . ($index + 1), $Var, 0);
            SetValue($this->GetIDForIdent("VArL" . ($index + 1)), $Var);
        }

        //PhaseAngle
        for ($index = 0; $index < 3; $index++)
        {
            $PhaseAngle = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 4, "Address" => 36 + ($index * 2), "Quantity" => 2, "Data" => "")));
            if ($PhaseAngle === false)
            {
                $this->unlock($IO);
                return false;
            }
            $PhaseAngle = unpack("G", substr($PhaseAngle, 2))[1];
            $this->SendDebug('PhaseAngle L' . ($index + 1), $PhaseAngle, 0);
            SetValue($this->GetIDForIdent("PhaseAngleL" . ($index + 1)), $PhaseAngle);
        }

        $Frequenz = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 4, "Address" => 70, "Quantity" => 2, "Data" => "")));
        if ($Frequenz === false)
        {
            $this->unlock($IO);
            return false;
        }
        $Frequenz = unpack("G", substr($Frequenz, 2))[1];
        $this->SendDebug('Frequenz', $Frequenz, 0);
        SetValue($this->GetIDForIdent("Frequenz"), $Frequenz);

        for ($index = 0; $index < 3; $index++)
        {
            $Total = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 4, "Address" => 376 + ($index * 2), "Quantity" => 2, "Data" => "")));
            if ($Total === false)
            {
                $this->unlock($IO);
                return false;
            }
            $Total = unpack("G", substr($Total, 2))[1];
            $this->SendDebug('Total L' . ($index + 1), $Total, 0);
            SetValue($this->GetIDForIdent("TotalL" . ($index + 1)), $Total);
        }

        IPS_Sleep(333);
        $this->unlock($IO);
        return true;
    }

    /**
     * Versucht eine Semaphore zu setzen und wiederholt dies bei Misserfolg bis zu 100 mal.
     * @param string $ident Ein String der den Lock bezeichnet.
     * @return boolean TRUE bei Erfolg, FALSE bei Misserfolg.
     */
    private function lock($ident)
    {
        for ($i = 0; $i < 100; $i++)
        {
            if (IPS_SemaphoreEnter('ModBus' . '.' . (string) $ident, 1))
            {
                return true;
            }
            else
            {
                IPS_Sleep(5);
            }
        }
        return false;
    }

    /**
     * Löscht eine Semaphore.
     * @param string $ident Ein String der den Lock bezeichnet.
     */
    private function unlock($ident)
    {
        IPS_SemaphoreLeave('ModBus' . '.' . (string) $ident);
    }

}

?>