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
require_once(__DIR__ . "/../libs/BGETechModule.php");  // diverse Klassen

/**
 * SDM230 ist die Klasse für die SDM230 ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech
 */
class SDM230 extends BGETech
{
    const PREFIX = 'SDM230';

    public static $Variables = [
        ['Voltage', vtFloat, 'Volt.230', 0x0000, 4, 2, true],
        ['Current', vtFloat, 'Ampere', 0x0006, 4, 2, true],
        ['Active power', vtFloat, 'Watt.14490', 0x000C, 4, 2, true],
        ['Apparent power', vtFloat, 'VA', 0x0012, 4, 2, true],
        ['Reactive power', vtFloat, 'VaR', 0x0018, 4, 2, true],
        ['Power factor', vtFloat, '', 0x001E, 4, 2, true],
        ['Phase angle', vtFloat, 'PhaseAngle', 0x0024, 4, 2, true],
        ['Frequency', vtFloat, 'Hertz.50', 0x0046, 4, 2, true],
        ['Total system power demand', vtFloat, 'Watt.14490', 0x0054, 4, 2, true],
        ['Maximum total system power demand', vtFloat, 'Watt.14490', 0x0056, 4, 2, true],
        ['Current demand', vtFloat, 'Ampere', 0x0102, 4, 2, true],
        ['Maximum current demand', vtFloat, 'Ampere', 0x0108, 4, 2, true],
        ['Total active energy', vtFloat, 'Electricity', 0x0156, 4, 2, true],
        ['Total reactive energy', vtFloat, 'kVArh', 0x0158, 4, 2, true]
    ];
}
