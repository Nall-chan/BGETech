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
 * @version       3.00
 *
 */
require_once __DIR__ . '/../libs/BGETechModule.php';  // diverse Klassen

/**
 * SDM120C ist die Klasse für die SDM120C ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech.
 */
class SDM120C extends BGETech
{
    const PREFIX = 'SDM120C';

    public static $Variables = [
        ['Voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x0000, 4, 2, true],
        ['Current', VARIABLETYPE_FLOAT, 'Ampere', 0x0006, 4, 2, true],
        ['Active power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x000C, 4, 2, true],
        ['Apparent power', VARIABLETYPE_FLOAT, 'VA', 0x0012, 4, 2, true],
        ['Reactive power', VARIABLETYPE_FLOAT, 'VaR', 0x0018, 4, 2, true],
        ['Power factor', VARIABLETYPE_FLOAT, '', 0x001E, 4, 2, true],
        ['Frequency', VARIABLETYPE_FLOAT, 'Hertz.50', 0x0046, 4, 2, true],
        ['Total import energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0048, 4, 2, true],
        ['Total export energy', VARIABLETYPE_FLOAT, 'Electricity', 0x004A, 4, 2, true],
        ['Total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0156, 4, 2, true]
    ];
}
