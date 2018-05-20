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
 * SDM72D ist die Klasse für die SDM72D ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech
 */
class SDM72D extends BGETech
{
    const PREFIX = 'SDM72D';

    static $Variables = [
        ['Power', vtFloat, 'Watt.14490', 0x0034, 4, 2],
        ['Total active energy', vtFloat, 'Electricity', 0x0156, 4, 2]
    ];

}
