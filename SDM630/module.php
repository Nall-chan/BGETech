<?php

declare(strict_types=1);

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2019 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.10
 *
 */
require_once __DIR__ . '/../libs/BGETechModule.php';  // diverse Klassen

/**
 * SDM630 ist die Klasse für die SDM630 ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech.
 */
class SDM630 extends BGETech
{
    const PREFIX = 'SDM630';

    public static $Variables = [
        ['Voltage L1', VARIABLETYPE_FLOAT, 'Volt.230', 0x0000, 4, 2, true],
        ['Voltage L2', VARIABLETYPE_FLOAT, 'Volt.230', 0x0002, 4, 2, true],
        ['Voltage L3', VARIABLETYPE_FLOAT, 'Volt.230', 0x0004, 4, 2, true],
        ['Current L1', VARIABLETYPE_FLOAT, 'Ampere', 0x0006, 4, 2, true],
        ['Current L2', VARIABLETYPE_FLOAT, 'Ampere', 0x0008, 4, 2, true],
        ['Current L3', VARIABLETYPE_FLOAT, 'Ampere', 0x000A, 4, 2, true],
        ['Active power L1', VARIABLETYPE_FLOAT, 'Watt.14490', 0x000C, 4, 2, true],
        ['Active power L2', VARIABLETYPE_FLOAT, 'Watt.14490', 0x000E, 4, 2, true],
        ['Active power L3', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0010, 4, 2, true],
        ['Apparent power L1', VARIABLETYPE_FLOAT, 'VA', 0x0012, 4, 2, true],
        ['Apparent power L2', VARIABLETYPE_FLOAT, 'VA', 0x0014, 4, 2, true],
        ['Apparent power L3', VARIABLETYPE_FLOAT, 'VA', 0x0016, 4, 2, true],
        ['Reactive power L1', VARIABLETYPE_FLOAT, 'VaR', 0x0018, 4, 2, true],
        ['Reactive power L2', VARIABLETYPE_FLOAT, 'VaR', 0x001A, 4, 2, true],
        ['Reactive power L3', VARIABLETYPE_FLOAT, 'VaR', 0x001C, 4, 2, true],
        ['Power factor L1', VARIABLETYPE_FLOAT, '', 0x001E, 4, 2, true],
        ['Power factor L2', VARIABLETYPE_FLOAT, '', 0x0020, 4, 2, true],
        ['Power factor L3', VARIABLETYPE_FLOAT, '', 0x0022, 4, 2, true],
        ['Phase angle L1', VARIABLETYPE_FLOAT, 'PhaseAngle', 0x0024, 4, 2, true],
        ['Phase angle L2', VARIABLETYPE_FLOAT, 'PhaseAngle', 0x0026, 4, 2, true],
        ['Phase angle L3', VARIABLETYPE_FLOAT, 'PhaseAngle', 0x0028, 4, 2, true],
        ['Average line to neutral voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x002A, 4, 2, true],
        ['Average line current', VARIABLETYPE_FLOAT, 'Ampere', 0x002E, 4, 2, true],
        ['Sum of line currents', VARIABLETYPE_FLOAT, 'Ampere', 0x0030, 4, 2, true],
        ['Total system power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0034, 4, 2, true],
        ['Total system apparent power', VARIABLETYPE_FLOAT, 'VA', 0x0038, 4, 2, true],
        ['Total system reactive power', VARIABLETYPE_FLOAT, 'VaR', 0x003C, 4, 2, true],
        ['Total system power factor', VARIABLETYPE_FLOAT, '', 0x003E, 4, 2, true],
        ['Total system phase angle', VARIABLETYPE_FLOAT, 'PhaseAngle', 0x0042, 4, 2, true],
        ['Frequency', VARIABLETYPE_FLOAT, 'Hertz.50', 0x0046, 4, 2, true],
        ['Total system power demand', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0054, 4, 2, true],
        ['Maximum total system power demand', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0056, 4, 2, true],
        ['Total system apparent power demand', VARIABLETYPE_FLOAT, 'VA', 0x0064, 4, 2, true],
        ['Maximum total system apparent power demand', VARIABLETYPE_FLOAT, 'VA', 0x0066, 4, 2, true],
        ['Total neutral current demand', VARIABLETYPE_FLOAT, 'Ampere', 0x0068, 4, 2, true],
        ['Maximum neutral current demand', VARIABLETYPE_FLOAT, 'Ampere', 0x006A, 4, 2, true],
        ['Line 1 to Line 2 voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x00C8, 4, 2, true],
        ['Line 2 to Line 3 voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x00CA, 4, 2, true],
        ['Line 3 to Line 1 voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x00CC, 4, 2, true],
        ['Average line to line voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x00CE, 4, 2, true],
        ['Neutral current', VARIABLETYPE_FLOAT, 'Ampere', 0x00E0, 4, 2, true],
        ['Line 1 voltage THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x00EA, 4, 2, true],
        ['Line 2 voltage THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x00EC, 4, 2, true],
        ['Line 3 voltage THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x00EE, 4, 2, true],
        ['Line 1 Current THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x00F0, 4, 2, true],
        ['Line 2 Current THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x00F2, 4, 2, true],
        ['Line 3 Current THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x00F4, 4, 2, true],
        ['Average line to neutral voltage THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x00F8, 4, 2, true],
        ['Average line current THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x00FA, 4, 2, true],
        ['Total system power factor', VARIABLETYPE_FLOAT, 'PhaseAngle', 0x00FE, 4, 2, true],
        ['Line 1 current demand', VARIABLETYPE_FLOAT, 'Ampere', 0x0102, 4, 2, true],
        ['Line 2 current demand', VARIABLETYPE_FLOAT, 'Ampere', 0x0104, 4, 2, true],
        ['Line 3 current demand', VARIABLETYPE_FLOAT, 'Ampere', 0x0106, 4, 2, true],
        ['Maximum line 1 current demand', VARIABLETYPE_FLOAT, 'Ampere', 0x0108, 4, 2, true],
        ['Maximum line 2 current demand', VARIABLETYPE_FLOAT, 'Ampere', 0x010A, 4, 2, true],
        ['Maximum line 3 current demand', VARIABLETYPE_FLOAT, 'Ampere', 0x010C, 4, 2, true],
        ['Line 1 to line 2 voltage THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x014E, 4, 2, true],
        ['Line 2 to line 3 voltage THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x0150, 4, 2, true],
        ['Line 3 to line 1 voltage THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x0152, 4, 2, true],
        ['Average line to line voltage THD', VARIABLETYPE_FLOAT, 'Intensity.F', 0x0154, 4, 2, true],
        ['Total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0156, 4, 2, true],
        ['Total reactive energy', VARIABLETYPE_FLOAT, 'kVArh', 0x0158, 4, 2, true],
        ['L1 total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0166, 4, 2, true],
        ['L2 total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0168, 4, 2, true],
        ['L3 total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x016A, 4, 2, true],
        ['L1 total reactive energy', VARIABLETYPE_FLOAT, 'kVArh', 0x0178, 4, 2, true],
        ['L2 total reactive energy', VARIABLETYPE_FLOAT, 'kVArh', 0x017A, 4, 2, true],
        ['L3 total reactive energy', VARIABLETYPE_FLOAT, 'kVArh', 0x017C, 4, 2, true]
    ];
}
