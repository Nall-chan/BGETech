<?php

declare(strict_types=1);

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2021 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.30
 *
 */
require_once __DIR__ . '/../libs/BGETechModule.php';  // diverse Klassen

/**
 * DRS210C ist die Klasse für die DRS210-C ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech.
 */
class DRS210C extends BGETech
{
    const PREFIX = 'DRS210C';

    public static $Variables = [
        ['Voltage', VARIABLETYPE_FLOAT, 'Volt.230', 0x2000, 3, 2, true],
        ['Current', VARIABLETYPE_FLOAT, 'Ampere', 0x2060, 3, 2, true],
        ['Active power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x2080, 3, 2, true],
        ['Apparent power', VARIABLETYPE_FLOAT, 'VA', 0x20C0, 3, 2, true],
        ['Reactive power', VARIABLETYPE_FLOAT, 'VaR', 0x20A0, 3, 2, true],
        ['Power factor', VARIABLETYPE_FLOAT, '', 0x20E0, 3, 2, true],
        ['Frequency', VARIABLETYPE_FLOAT, 'Hertz.50', 0x2020, 3, 2, true],
        ['Total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x3000, 3, 2, true]
    ];
}
