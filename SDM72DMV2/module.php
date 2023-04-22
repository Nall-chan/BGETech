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
 * @version       3.50
 *
 */
require_once __DIR__ . '/../libs/BGETechModule.php';  // diverse Klassen

/**
 * SDM72DMV2 ist die Klasse für die SDM72DMV2 ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech.
 */
class SDM72DMV2 extends BGETech
{
    const PREFIX = 'SDM72DMV2';

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
        ['Average line to neutral voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x002A, 4, 2, true],
        ['Average line current', VARIABLETYPE_FLOAT, 'Ampere', 0x002E, 4, 2, true],
        ['Sum of line currents', VARIABLETYPE_FLOAT, 'Ampere', 0x0030, 4, 2, true],
        ['Total system power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0034, 4, 2, true],
        ['Total system apparent power', VARIABLETYPE_FLOAT, 'VA', 0x0038, 4, 2, true],
        ['Total system reactive power', VARIABLETYPE_FLOAT, 'VaR', 0x003C, 4, 2, true],
        ['Total system power factor', VARIABLETYPE_FLOAT, '', 0x003E, 4, 2, true],
        ['Frequency', VARIABLETYPE_FLOAT, 'Hertz.50', 0x0046, 4, 2, true],
        ['Total import energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0048, 4, 2, true],
        ['Total export energy', VARIABLETYPE_FLOAT, 'Electricity', 0x004A, 4, 2, true],
        ['Line 1 to Line 2 voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x00C8, 4, 2, true],
        ['Line 2 to Line 3 voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x00CA, 4, 2, true],
        ['Line 3 to Line 1 voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x00CC, 4, 2, true],
        ['Average line to line voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x00CE, 4, 2, true],
        ['Neutral current', VARIABLETYPE_FLOAT, 'Ampere', 0x00E0, 4, 2, true],
        ['Total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0156, 4, 2, true],
        ['Total reactive energy', VARIABLETYPE_FLOAT, 'kVArh', 0x0158, 4, 2, true],
        ['Resettable total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0180, 4, 2, true],
        ['Resettable import active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0184, 4, 2, true],
        ['Resettable export active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0186, 4, 2, true],
        ['Netto active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x018C, 4, 2, true],
        ['Import power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0500, 4, 2, true],
        ['Export power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0502, 4, 2, true]
    ];
}
