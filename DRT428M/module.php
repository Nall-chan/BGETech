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
 * @version       3.60
 *
 */
require_once __DIR__ . '/../libs/BGETechModule.php';  // diverse Klassen

/**
 * DRT428M ist die Klasse für die DRT428M ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech.
 */
class DRT428M extends BGETech
{
    public const PREFIX = 'DRT428M';

    public static $Variables = [
        ['Voltage L1', VARIABLETYPE_FLOAT, 'Volt.230', 0x000E, 3, 2, true],
        ['Voltage L2', VARIABLETYPE_FLOAT, 'Volt.230', 0x0010, 3, 2, true],
        ['Voltage L3', VARIABLETYPE_FLOAT, 'Volt.230', 0x0012, 3, 2, true],
        ['Frequency', VARIABLETYPE_FLOAT, 'Hertz.50', 0x0014, 3, 2, true],
        ['Current L1', VARIABLETYPE_FLOAT, 'Ampere', 0x0016, 3, 2, true],
        ['Current L2', VARIABLETYPE_FLOAT, 'Ampere', 0x0018, 3, 2, true],
        ['Current L3', VARIABLETYPE_FLOAT, 'Ampere', 0x001A, 3, 2, true],
        ['Total active power', VARIABLETYPE_FLOAT, 'Power', 0x001C, 3, 2, true],
        ['Active power L1', VARIABLETYPE_FLOAT, 'Power', 0x001E, 3, 2, true],
        ['Active power L2', VARIABLETYPE_FLOAT, 'Power', 0x0020, 3, 2, true],
        ['Active power L3', VARIABLETYPE_FLOAT, 'Power', 0x0022, 3, 2, true],
        ['Total reactive power', VARIABLETYPE_FLOAT, 'VaR', 0x0024, 3, 2, true],
        ['Reactive power L1', VARIABLETYPE_FLOAT, 'VaR', 0x0026, 3, 2, true],
        ['Reactive power L2', VARIABLETYPE_FLOAT, 'VaR', 0x0028, 3, 2, true],
        ['Reactive power L3', VARIABLETYPE_FLOAT, 'VaR', 0x002A, 3, 2, true],
        ['Total apparent power', VARIABLETYPE_FLOAT, 'VA', 0x002C, 3, 2, true],
        ['Apparent power L1', VARIABLETYPE_FLOAT, 'VA', 0x002E, 3, 2, true],
        ['Apparent power L2', VARIABLETYPE_FLOAT, 'VA', 0x0030, 3, 2, true],
        ['Apparent power L3', VARIABLETYPE_FLOAT, 'VA', 0x0032, 3, 2, true],
        ['Total power factor', VARIABLETYPE_FLOAT, '', 0x0034, 3, 2, true],
        ['Power factor L1', VARIABLETYPE_FLOAT, '', 0x0036, 3, 2, true],
        ['Power factor L2', VARIABLETYPE_FLOAT, '', 0x0038, 3, 2, true],
        ['Power factor L3', VARIABLETYPE_FLOAT, '', 0x003A, 3, 2, true],
        ['Total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0100, 3, 2, true],
        ['Total active energy L1', VARIABLETYPE_FLOAT, 'Electricity', 0x0102, 3, 2, true],
        ['Total active energy L2', VARIABLETYPE_FLOAT, 'Electricity', 0x0104, 3, 2, true],
        ['Total active energy L3', VARIABLETYPE_FLOAT, 'Electricity', 0x0106, 3, 2, true],
        ['Import active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0108, 3, 2, true],
        ['Import active energy L1', VARIABLETYPE_FLOAT, 'Electricity', 0x010A, 3, 2, true],
        ['Import active energy L2', VARIABLETYPE_FLOAT, 'Electricity', 0x010C, 3, 2, true],
        ['Import active energy L3', VARIABLETYPE_FLOAT, 'Electricity', 0x010E, 3, 2, true],
        ['Export active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0110, 3, 2, true],
        ['Export active energy L1', VARIABLETYPE_FLOAT, 'Electricity', 0x0112, 3, 2, true],
        ['Export active energy L2', VARIABLETYPE_FLOAT, 'Electricity', 0x0114, 3, 2, true],
        ['Export active energy L3', VARIABLETYPE_FLOAT, 'Electricity', 0x0116, 3, 2, true],
        ['Total reactive energy', VARIABLETYPE_FLOAT, 'kVArh', 0x0118, 3, 2, true],
        ['Total reactive energy L1', VARIABLETYPE_FLOAT, 'kVArh', 0x011A, 3, 2, true],
        ['Total reactive energy L2', VARIABLETYPE_FLOAT, 'kVArh', 0x011C, 3, 2, true],
        ['Total reactive energy L3', VARIABLETYPE_FLOAT, 'kVArh', 0x011E, 3, 2, true],
        ['Import reactive energy', VARIABLETYPE_FLOAT, 'kVArh', 0x0120, 3, 2, true],
        ['Import reactive energy L1', VARIABLETYPE_FLOAT, 'kVArh', 0x0122, 3, 2, true],
        ['Import reactive energy L2', VARIABLETYPE_FLOAT, 'kVArh', 0x0124, 3, 2, true],
        ['Import reactive energy L3', VARIABLETYPE_FLOAT, 'kVArh', 0x0126, 3, 2, true],
        ['Export reactive energy', VARIABLETYPE_FLOAT, 'kVArh', 0x0128, 3, 2, true],
        ['Export reactive energy L1', VARIABLETYPE_FLOAT, 'kVArh', 0x012A, 3, 2, true],
        ['Export reactive energy L2', VARIABLETYPE_FLOAT, 'kVArh', 0x012C, 3, 2, true],
        ['Export reactive energy L3', VARIABLETYPE_FLOAT, 'kVArh', 0x012E, 3, 2, true]
    ];
}
