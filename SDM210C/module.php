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
 * SDM210C ist die Klasse für die SDM210C ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech
 */
class SDM210C extends BGETech
{
    const PREFIX = 'SDM210C';

    public static $Variables = [
        ['Voltage', vtFloat, 'Volt.230', 0x0000, 4, 2],
        ['Current', vtFloat, 'Ampere', 0x0006, 4, 2],
        ['Active power', vtFloat, 'Watt.14490', 0x000C, 4, 2],
        ['Apparent power', vtFloat, 'VA', 0x0012, 4, 2],
        ['Reactive power', vtFloat, 'VaR', 0x0018, 4, 2],
        ['Power factor', vtFloat, '', 0x001E, 4, 2],
        ['Frequency', vtFloat, 'Hertz.50', 0x0046, 4, 2],
        ['Total active energy', vtFloat, 'Electricity', 0x0156, 4, 2]
    ];
}
