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
 * SDM630 ist die Klasse für die SDM630 ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech
 */
class SDM630 extends BGETech
{
    const PREFIX = 'SDM630';

    static $Variables = [
        ['Voltage L1', vtFloat, 'Volt.230', 0x0000, 4, 2],
        ['Voltage L2', vtFloat, 'Volt.230', 0x0002, 4, 2],
        ['Voltage L3', vtFloat, 'Volt.230', 0x0004, 4, 2],
        ['Current L1', vtFloat, 'Ampere', 0x0006, 4, 2],
        ['Current L2', vtFloat, 'Ampere', 0x0008, 4, 2],
        ['Current L3', vtFloat, 'Ampere', 0x000A, 4, 2],
        ['Active power L1', vtFloat, 'Watt.14490', 0x000C, 4, 2],
        ['Active power L2', vtFloat, 'Watt.14490', 0x000E, 4, 2],
        ['Active power L3', vtFloat, 'Watt.14490', 0x0010, 4, 2],
        ['Apparent power L1', vtFloat, 'VA', 0x0012, 4, 2],
        ['Apparent power L2', vtFloat, 'VA', 0x0014, 4, 2],
        ['Apparent power L3', vtFloat, 'VA', 0x0016, 4, 2],
        ['Reactive power L1', vtFloat, 'VaR', 0x0018, 4, 2],
        ['Reactive power L2', vtFloat, 'VaR', 0x001A, 4, 2],
        ['Reactive power L3', vtFloat, 'VaR', 0x001C, 4, 2],
        ['Power factor L1', vtFloat, '', 0x001E, 4, 2],
        ['Power factor L2', vtFloat, '', 0x0020, 4, 2],
        ['Power factor L3', vtFloat, '', 0x0022, 4, 2],
        ['Phase angle L1', vtFloat, 'PhaseAngle', 0x0024, 4, 2],
        ['Phase angle L2', vtFloat, 'PhaseAngle', 0x0026, 4, 2],
        ['Phase angle L3', vtFloat, 'PhaseAngle', 0x0028, 4, 2],
        ['Average line to neutral voltage', vtFloat, 'Volt.230', 0x002A, 4, 2],
        ['Average line current', vtFloat, 'Ampere', 0x002E, 4, 2],
        ['Sum of line currents', vtFloat, 'Ampere', 0x0030, 4, 2],
        ['Total system power', vtFloat, 'Watt.14490', 0x0034, 4, 2],
        ['Total system apparent power', vtFloat, 'VA', 0x0038, 4, 2],
        ['Total system reactive power', vtFloat, 'VaR', 0x003C, 4, 2],
        ['Total system power factor', vtFloat, '', 0x003E, 4, 2],
        ['Total system phase angle', vtFloat, 'PhaseAngle', 0x0042, 4, 2],
        ['Frequency', vtFloat, 'Hertz.50', 0x0046, 4, 2],
        ['Total system power demand', vtFloat, 'Watt.14490', 0x0054, 4, 2],
        ['Maximum total system power demand', vtFloat, 'Watt.14490', 0x0056, 4, 2],
        ['Total system apparent power demand', vtFloat, 'VA', 0x0064, 4, 2],
        ['Maximum total system apparent power demand', vtFloat, 'VA', 0x0066, 4, 2],
        ['Total neutral current demand', vtFloat, 'Ampere', 0x0068, 4, 2],
        ['Maximum neutral current demand', vtFloat, 'Ampere', 0x006A, 4, 2],
        ['Line 1 to Line 2 voltage', vtFloat, 'Volt.230', 0x00C8, 4, 2],
        ['Line 2 to Line 3 voltage', vtFloat, 'Volt.230', 0x00CA, 4, 2],
        ['Line 3 to Line 1 voltage', vtFloat, 'Volt.230', 0x00CC, 4, 2],
        ['Average line to line voltage', vtFloat, 'Volt.230', 0x00CE, 4, 2],
        ['Neutral current', vtFloat, 'Ampere', 0x00E0, 4, 2],
        ['Line 1 voltage THD', vtFloat, 'Intensity.F', 0x00EA, 4, 2],
        ['Line 2 voltage THD', vtFloat, 'Intensity.F', 0x00EC, 4, 2],
        ['Line 3 voltage THD', vtFloat, 'Intensity.F', 0x00EE, 4, 2],
        ['Line 1 Current THD', vtFloat, 'Intensity.F', 0x00F0, 4, 2],
        ['Line 2 Current THD', vtFloat, 'Intensity.F', 0x00F2, 4, 2],
        ['Line 3 Current THD', vtFloat, 'Intensity.F', 0x00F4, 4, 2],
        ['Average line to neutral voltage THD', vtFloat, 'Intensity.F', 0x00F8, 4, 2],
        ['Average line current THD', vtFloat, 'Intensity.F', 0x00FA, 4, 2],
        ['Total system power factor', vtFloat, 'PhaseAngle', 0x00FE, 4, 2],
        ['Line 1 current demand', vtFloat, 'Ampere', 0x0102, 4, 2],
        ['Line 2 current demand', vtFloat, 'Ampere', 0x0104, 4, 2],
        ['Line 3 current demand', vtFloat, 'Ampere', 0x0106, 4, 2],
        ['Maximum line 1 current demand', vtFloat, 'Ampere', 0x0108, 4, 2],
        ['Maximum line 2 current demand', vtFloat, 'Ampere', 0x010A, 4, 2],
        ['Maximum line 3 current demand', vtFloat, 'Ampere', 0x010C, 4, 2],
        ['Line 1 to line 2 voltage THD', vtFloat, 'Intensity.F', 0x014E, 4, 2],
        ['Line 2 to line 3 voltage THD', vtFloat, 'Intensity.F', 0x0150, 4, 2],
        ['Line 3 to line 1 voltage THD', vtFloat, 'Intensity.F', 0x0152, 4, 2],
        ['Average line to line voltage THD', vtFloat, 'Intensity.F', 0x0154, 4, 2],
        ['Total active energy', vtFloat, 'Electricity', 0x0156, 4, 2],
        ['Total reactive energy', vtFloat, 'kVArh', 0x0158, 4, 2],
        ['L1 total active energy', vtFloat, 'Electricity', 0x0166, 4, 2],
        ['L2 total active energy', vtFloat, 'Electricity', 0x0168, 4, 2],
        ['L3 total active energy', vtFloat, 'Electricity', 0x0168A, 4, 2],
        ['L1 total reactive energy', vtFloat, 'kVArh', 0x0178, 4, 2],
        ['L2 total reactive energy', vtFloat, 'kVArh', 0x017A, 4, 2],
        ['L3 total reactive energy', vtFloat, 'kVArh', 0x017C, 4, 2]
    ];

}
