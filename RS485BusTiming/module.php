<?php

declare(strict_types=1);

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2026 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       4.00
 *
 */
require_once dirname(__DIR__) . '/libs/RS485BusTimingConsts.php';
eval('declare(strict_types=1);namespace RS485BusTiming {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/BufferHelper.php') . '}');
eval('declare(strict_types=1);namespace RS485BusTiming {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/DebugHelper.php') . '}');

use RS485BusTiming\Defaults;
use RS485BusTiming\GUID;
use RS485BusTiming\Locks;
use RS485BusTiming\Property;

/**
 * RS485BusTiming steuert den Zugriff mehrerer ModBus Gateways auf einen gemeinsamen RS485-Bus.
 *
 * Der Splitter sitzt zwischen den ModBus Gateways und dem IO (Serial Port oder Client Socket).
 * Er reicht alle Daten unverändert durch, lässt aber immer nur eine Anfrage gleichzeitig auf den Bus.
 * Die nächste Anfrage wird erst gesendet, wenn
 * - nach einer Antwort der Bus für BusIdleTime ms ruhig war, oder
 * - nach einer Anfrage ohne Antwort ResponseTimeout ms vergangen sind.
 *
 * @property float $LastTransmit Zeitpunkt (microtime) der letzten gesendeten Anfrage
 * @property float $LastReceive Zeitpunkt (microtime) der letzten empfangenen Daten
 */
class RS485BusTiming extends IPSModuleStrict
{
    use \RS485BusTiming\BufferHelper;
    use \RS485BusTiming\DebugHelper;

    /**
     * Interne Funktion des SDK.
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyInteger(Property::BusIdleTime, Defaults::BusIdleTime);
        $this->RegisterPropertyInteger(Property::ResponseTimeout, Defaults::ResponseTimeout);
        $this->LastTransmit = 0.0;
        $this->LastReceive = 0.0;
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        $this->LastTransmit = 0.0;
        $this->LastReceive = 0.0;
        $this->SetStatus(IS_ACTIVE);
    }

    /**
     * Interne Funktion des SDK.
     * Als IO kommen ein Serial Port oder ein Client Socket (ModBus RTU über TCP) in Frage.
     */
    public function GetCompatibleParents(): string
    {
        return json_encode([
            'type'      => 'require',
            'moduleIDs' => [GUID::SerialPort, GUID::ClientSocket]
        ]);
    }

    /**
     * Interne Funktion des SDK.
     * Empfängt eine Anfrage eines ModBus Gateway und sendet sie, sobald der Bus frei ist.
     *
     * @param string $JSONString Daten des Gateway (DataID SendToIO, Buffer HEX-kodiert).
     *
     * @return string Rückgabe des IO.
     */
    public function ForwardData(string $JSONString): string
    {
        if (!$this->HasActiveParent()) {
            trigger_error($this->Translate('Instance has no active parent.'), E_USER_NOTICE);
            return '';
        }
        $IdleTime = $this->ReadPropertyInteger(Property::BusIdleTime);
        $Timeout = $this->ReadPropertyInteger(Property::ResponseTimeout);
        // Eine wartende Anfrage muss im ungünstigsten Fall alle vor ihr eingereihten abwarten.
        $LockTimeout = 10 * ($IdleTime + $Timeout);
        if (!IPS_SemaphoreEnter($this->LockName(), $LockTimeout)) {
            $this->SendDebug('Lock', 'Timeout', 0);
            trigger_error($this->Translate('Bus is busy.'), E_USER_NOTICE);
            return '';
        }
        try {
            $Waited = $this->WaitForBusIdle($IdleTime / 1000, $Timeout / 1000);
            $this->SendDebug('Transmit (waited ' . $Waited . ' ms)', $this->DecodeBuffer($JSONString), 1);
            // Zeitpunkt vor dem Senden setzen, damit eine sehr schnelle Antwort nicht davor liegt.
            $this->LastTransmit = microtime(true);
            return $this->SendDataToParent($JSONString);
        } finally {
            IPS_SemaphoreLeave($this->LockName());
        }
    }

    /**
     * Interne Funktion des SDK.
     * Empfängt Daten vom IO und reicht sie an alle Gateways weiter.
     *
     * @param string $JSONString Daten des IO (DataID ReceiveFromIO, Buffer HEX-kodiert).
     *
     * @return string Immer leer.
     */
    public function ReceiveData(string $JSONString): string
    {
        $this->LastReceive = microtime(true);
        $this->SendDebug('Receive', $this->DecodeBuffer($JSONString), 1);
        $this->SendDataToChildren($JSONString);
        return '';
    }

    /**
     * Wartet, bis der Bus frei ist.
     *
     * Frei ist der Bus, wenn nach der letzten Anfrage eine Antwort kam und seitdem $IdleTime Sekunden
     * ohne weitere Daten vergangen sind, oder wenn auf die letzte Anfrage $Timeout Sekunden keine Antwort kam.
     *
     * @param float $IdleTime Ruhezeit nach dem letzten empfangenen Byte in Sekunden.
     * @param float $Timeout Freigabe ohne Antwort nach Sekunden.
     *
     * @return int Gewartete Zeit in ms.
     */
    private function WaitForBusIdle(float $IdleTime, float $Timeout): int
    {
        $Start = microtime(true);
        while (true) {
            $LastTransmit = $this->LastTransmit;
            $LastReceive = $this->LastReceive;
            if ($LastReceive >= $LastTransmit) {
                $FreeAt = $LastReceive + $IdleTime;
            } else {
                $FreeAt = $LastTransmit + $Timeout;
            }
            $Now = microtime(true);
            if ($Now >= $FreeAt) {
                return (int) round(($Now - $Start) * 1000);
            }
            usleep(min(Defaults::PollStep, (int) ceil(($FreeAt - $Now) * 1000000)));
        }
    }

    /**
     * Liefert die Nutzdaten eines Datenpakets für die Debug-Ausgabe.
     *
     * @param string $JSONString Datenpaket, Buffer HEX-kodiert (IPSModuleStrict).
     *
     * @return string Nutzdaten als Bytes.
     */
    private function DecodeBuffer(string $JSONString): string
    {
        $Data = json_decode($JSONString, true);
        if (!is_array($Data) || !isset($Data['Buffer'])) {
            return '';
        }
        $Bytes = @hex2bin($Data['Buffer']);
        return $Bytes === false ? '' : $Bytes;
    }

    /**
     * Name der Semaphore für den Buszugriff dieser Instanz.
     *
     * @return string Name der Semaphore.
     */
    private function LockName(): string
    {
        return 'RS485BusTiming.' . $this->InstanceID . '.' . Locks::Bus;
    }
}

/* @} */
