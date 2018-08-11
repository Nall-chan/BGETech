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
 * @version       2.04
 *
 */
require_once(__DIR__ . "/../libs/BGETechModule.php");  // diverse Klassen

/**
 * DRT710M ist die Klasse für die DRT710M ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech
 */
class DRT710M extends BGETech
{
    const PREFIX = 'DRT710M';

    public static $Variables = [
        ['Voltage L1', vtFloat, 'Volt.230', 0x0010, 4, 2, true],
        ['Voltage L2', vtFloat, 'Volt.230', 0x0012, 4, 2, true],
        ['Voltage L3', vtFloat, 'Volt.230', 0x0014, 4, 2, true],
        ['Frequency', vtFloat, 'Hertz.50', 0x004E, 4, 2, true],
        ['Current L1', vtFloat, 'Ampere', 0x0050, 4, 2, true],
        ['Current L2', vtFloat, 'Ampere', 0x0052, 4, 2, true],
        ['Current L3', vtFloat, 'Ampere', 0x0054, 4, 2, true],
        ['Neutral current', vtFloat, 'Ampere', 0x0056, 4, 2, true],
        ['Active power L1', vtFloat, 'Watt.14490', 0x0090, 4, 2, true],
        ['Active power L2', vtFloat, 'Watt.14490', 0x0092, 4, 2, true],
        ['Active power L3', vtFloat, 'Watt.14490', 0x0094, 4, 2, true],
        ['Total system power', vtFloat, 'Watt.14490', 0x0096, 4, 2, true],
        ['Apparent power L1', vtFloat, 'VA', 0x00D0, 4, 2, true],
        ['Apparent power L2', vtFloat, 'VA', 0x00D2, 4, 2, true],
        ['Apparent power L3', vtFloat, 'VA', 0x00D4, 4, 2, true],
        ['Total system apparent power', vtFloat, 'VA', 0x00D6, 4, 2, true],
        ['Reactive power L1', vtFloat, 'VaR', 0x0110, 4, 2, true],
        ['Reactive power L2', vtFloat, 'VaR', 0x0112, 4, 2, true],
        ['Reactive power L3', vtFloat, 'VaR', 0x0114, 4, 2, true],
        ['Total system reactive power', vtFloat, 'VaR', 0x0116, 4, 2, true],
        ['Power factor L1', vtFloat, '', 0x0150, 4, 2, true],
        ['Power factor L2', vtFloat, '', 0x0152, 4, 2, true],
        ['Power factor L3', vtFloat, '', 0x0154, 4, 2, true],
        ['Total system power factor', vtFloat, '', 0x0156, 4, 2, true],
        ['Total import energy', vtFloat, 'Electricity', 0x0160, 4, 2, true],
        ['Total export energy', vtFloat, 'Electricity', 0x0166, 4, 2, true]
    ];
}
