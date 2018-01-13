<?

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.1
 *
 */
require_once(__DIR__ . "/../libs/BGETechTraits.php");  // diverse Klassen

/**
 * DRS210C ist die Klasse für die DRS210-C ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert ipsmodule 
 */
class DRS210C extends IPSModule
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
        $this->RegisterTimer("UpdateTimer", 0, 'DRS210C_RequestRead($_IPS["TARGET"]);');
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->RegisterProfileFloat('VaR', '', '', ' var', 0, 0, 0, 2);
        $this->RegisterProfileFloat('VA', '', '', ' VA', 0, 0, 0, 2);

        $this->RegisterVariableFloat("Volt", "Volt", "Volt.230", 1);
        $this->RegisterVariableFloat("Ampere", "Ampere", "Ampere", 2);
        $this->RegisterVariableFloat("Frequenz", "Frequenz", "Hertz.50", 3);
        $this->RegisterVariableFloat("Watt", "Watt", "Watt.14490", 4);
        $this->RegisterVariableFloat("Var", "VaR", "VaR", 5);
        $this->RegisterVariableFloat("Va", "VA", "VA", 6);
        $this->RegisterVariableFloat("Total", "Total kWh", "Electricity", 7);

        if ($this->ReadPropertyInteger("Interval") > 0)
            $this->SetTimerInterval("UpdateTimer", $this->ReadPropertyInteger("Interval"));
        else
            $this->SetTimerInterval("UpdateTimer", 0);
    }

    /**
     * IPS-Instanz Funktion DRS210C_RequestRead.
     * Ließt alle Werte aus dem Gerät.
     *
     * @access public
     * @return bool True wenn Befehl erfolgreich ausgeführt wurde, sonst false.
     */
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

        $Volt = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 3, "Address" => 0x2000, "Quantity" => 2, "Data" => "")));
        if ($Volt === false)
        {
            $this->unlock($IO);
            return false;
        }
        $Volt = unpack("f", strrev(substr($Volt, 2)))[1];
        $this->SendDebug('Volt', $Volt, 0);
        SetValue($this->GetIDForIdent("Volt"), $Volt);


        $Frequenz = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 3, "Address" => 0x2020, "Quantity" => 2, "Data" => "")));
        if ($Frequenz === false)
        {
            $this->unlock($IO);
            return false;
        }
        $Frequenz = unpack("f", strrev(substr($Frequenz, 2)))[1];
        $this->SendDebug('Frequenz', $Frequenz, 0);
        SetValue($this->GetIDForIdent("Frequenz"), $Frequenz);


        $Ampere = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 3, "Address" => 0x2060, "Quantity" => 2, "Data" => "")));
        if ($Ampere === false)
        {
            $this->unlock($IO);
            return false;
        }
        $Ampere = unpack("f", strrev(substr($Ampere, 2)))[1];
        $this->SendDebug('Ampere', $Ampere, 0);
        SetValue($this->GetIDForIdent("Ampere"), $Ampere);


        $Watt = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 3, "Address" => 0x2080, "Quantity" => 2, "Data" => "")));
        if ($Watt === false)
        {
            $this->unlock($IO);
            return false;
        }
        $Watt = unpack("f", strrev(substr($Watt, 2)))[1];
        $this->SendDebug('Watt', $Watt, 0);
        SetValue($this->GetIDForIdent("Watt"), $Watt);


        // Blindleistung
        $Var = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 3, "Address" => 0x20A0, "Quantity" => 2, "Data" => "")));
        if ($Var === false)
        {
            $this->unlock($IO);
            return false;
        }
        $Var = unpack("f", strrev(substr($Var, 2)))[1];
        $this->SendDebug('Var', $Var, 0);
        SetValue($this->GetIDForIdent("Var"), $Var);


        //Scheinleistung 
        $Va = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 3, "Address" => 0x20C0, "Quantity" => 2, "Data" => "")));
        if ($Va === false)
        {
            $this->unlock($IO);
            return false;
        }
        $Va = unpack("f", strrev(substr($Va, 2)))[1];
        $this->SendDebug('Va', $Va, 0);
        SetValue($this->GetIDForIdent("Va"), $Va);


        $Total = $this->SendDataToParent(json_encode(Array("DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => 3, "Address" => 0x3000, "Quantity" => 2, "Data" => "")));
        if ($Total === false)
        {
            $this->unlock($IO);
            return false;
        }
        $Total = unpack("f", strrev(substr($Total, 2)))[1];
        $this->SendDebug('Total', $Total, 0);
        SetValue($this->GetIDForIdent("Total"), $Total);

        IPS_Sleep(333);
        $this->unlock($IO);
        return true;
    }

}

?>