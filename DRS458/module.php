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
 * DRS458 ist die Klasse für die DRS458 ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech
 */
class DRS458 extends BGETech
{
    const PREFIX = 'DRS458';

    static $Variables = [
        ['Total active energy', vtFloat, 'Electricity', 0x0000, 3, 2]
    ];

}
