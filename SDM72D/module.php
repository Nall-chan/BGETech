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
 * SDM72D ist die Klasse für die SDM72D ModBus Energie-Zähler der Firma B+G E-Tech
 * Erweitert BGETech.
 */
class SDM72D extends BGETech
{
    const PREFIX = 'SDM72D';

    public static $Variables = [
        ['Power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0034, 4, 2, true],
        ['Total import energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0048, 4, 2, true],
        ['Total export energy', VARIABLETYPE_FLOAT, 'Electricity', 0x004A, 4, 2, true],
        ['Total active energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0156, 4, 2, true],
        ['Settable total energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0180, 4, 2, true],
        ['Settable import energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0184, 4, 2, true],
        ['Settable export energy', VARIABLETYPE_FLOAT, 'Electricity', 0x0186, 4, 2, true],
        ['Import Power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0500, 4, 2, true],
        ['Export Power', VARIABLETYPE_FLOAT, 'Watt.14490', 0x0502, 4, 2, true]
    ];
}
