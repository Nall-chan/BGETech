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
 * @version       2.05
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
    const Swap = false;

    public static $Variables = [
        ['Voltage L1', vtInteger, 'Volt.I', 0x0010, 3, 2, true],
        ['Voltage L2', vtInteger, 'Volt.I', 0x0012, 3, 2, true],
        ['Voltage L3', vtInteger, 'Volt.I', 0x0014, 3, 2, true],
        ['Frequency', vtInteger, 'Hertz.I', 0x004E, 3, 2, true],
        ['Current L1', vtInteger, 'Ampere.I', 0x0050, 3, 2, true],
        ['Current L2', vtInteger, 'Ampere.I', 0x0052, 3, 2, true],
        ['Current L3', vtInteger, 'Ampere.I', 0x0054, 3, 2, true],
        ['Neutral current', vtInteger, 'Ampere.I', 0x0056, 3, 2, true],
        ['Active power L1', vtInteger, 'Watt.I', 0x0090, 3, 2, true],
        ['Active power L2', vtInteger, 'Watt.I', 0x0092, 3, 2, true],
        ['Active power L3', vtInteger, 'Watt.I', 0x0094, 3, 2, true],
        ['Total system power', vtInteger, 'Watt.I', 0x0096, 3, 2, true],
        ['Apparent power L1', vtInteger, 'VA.I', 0x00D0, 3, 2, true],
        ['Apparent power L2', vtInteger, 'VA.I', 0x00D2, 3, 2, true],
        ['Apparent power L3', vtInteger, 'VA.I', 0x00D4, 3, 2, true],
        ['Total system apparent power', vtInteger, 'VA.I', 0x00D6, 3, 2, true],
        ['Reactive power L1', vtInteger, 'VaR.I', 0x0110, 3, 2, true],
        ['Reactive power L2', vtInteger, 'VaR.I', 0x0112, 3, 2, true],
        ['Reactive power L3', vtInteger, 'VaR.I', 0x0114, 3, 2, true],
        ['Total system reactive power', vtInteger, 'VaR.I', 0x0116, 3, 2, true],
        ['Power factor L1', vtInteger, '', 0x0150, 3, 2, true],
        ['Power factor L2', vtInteger, '', 0x0152, 3, 2, true],
        ['Power factor L3', vtInteger, '', 0x0154, 3, 2, true],
        ['Total system power factor', vtInteger, '', 0x0156, 3, 2, true],
        ['Total import energy', vtInteger, 'Electricity.I', 0x0160, 3, 2, true],
        ['Total export energy', vtInteger, 'Electricity.I', 0x0166, 3, 2, true]
    ];
}
