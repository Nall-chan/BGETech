<?php

declare(strict_types=1);

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          RS485BusTimingConsts.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2026 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       4.00
 *
 */

namespace RS485BusTiming;

/**
 * GUIDs der Datenfluss-Schnittstellen und der kompatiblen IO-Module.
 */
class GUID
{
    public const SerialPort = '{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}';
    public const ClientSocket = '{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}';
    /** Daten zum IO (Serial Port, Client Socket) */
    public const SendToIO = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
    /** Daten vom IO zu den Gateways */
    public const ReceiveFromIO = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';
}

/**
 * Namen der Eigenschaften.
 */
class Property
{
    /** Ruhezeit auf dem Bus nach dem letzten empfangenen Byte in ms */
    public const BusIdleTime = 'BusIdleTime';
    /** Freigabe des Bus ohne Antwort nach ms */
    public const ResponseTimeout = 'ResponseTimeout';
}

/**
 * Standardwerte und Grenzen der Eigenschaften.
 */
class Defaults
{
    /** Live gemessen mit 4 × DRS210C: ab 100 ms antworten alle Zähler zuverlässig */
    public const BusIdleTime = 100;
    public const ResponseTimeout = 1000;
    public const MaxBusIdleTime = 5000;
    public const MinResponseTimeout = 100;
    public const MaxResponseTimeout = 10000;
    /** Wartezeit in µs zwischen zwei Prüfungen, ob der Bus frei ist */
    public const PollStep = 5000;
}

/**
 * Namen der Locks.
 */
class Locks
{
    public const Bus = 'Bus';
}

/* @} */
