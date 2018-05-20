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
 * DRS210C ist die Klasse für die DRS210-C ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech
 */
class DRS210C extends BGETech
{
    const PREFIX = 'DRS210C';

    static $Variables = [
        ['Voltage', vtFloat, 'Volt.230', 0x2000, 3, 2],
        ['Current', vtFloat, 'Ampere', 0x2060, 3, 2],
        ['Active power', vtFloat, 'Watt.14490', 0x2080, 3, 2],
        ['Apparent power', vtFloat, 'VA', 0x20C0, 3, 2],
        ['Reactive power', vtFloat, 'VaR', 0x20A0, 3, 2],
        ['Power factor', vtFloat, '', 0x20E0, 3, 2],
        ['Frequency', vtFloat, 'Hertz.50', 0x2020, 3, 2],
        ['Total kwh', vtFloat, 'Electricity', 0x3000, 3, 2]
    ];

}
