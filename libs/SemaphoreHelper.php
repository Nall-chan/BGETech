<?php

declare(strict_types=1);

/*
 * @addtogroup bgetech
 * @{
 *
 * @package       BGETech
 * @file          BGETechTraits.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2022 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.60
 *
 */

namespace BGETech;

/**
 * Biete Funktionen um Thread-Safe auf Objekte zuzugreifen.
 */
trait SemaphoreHelper
{
    /**
     * Versucht eine Semaphore zu setzen und wiederholt dies bei Misserfolg bis zu 100 mal.
     *
     * @param int $ident Ein String der den Lock bezeichnet.
     *
     * @return bool TRUE bei Erfolg, FALSE bei Misserfolg.
     */
    private function lock(int $ident): bool
    {
        for ($i = 0; $i < 100; $i++) {
            if (IPS_SemaphoreEnter('ModBus.' . (string) $ident, 1)) {
                return true;
            } else {
                IPS_Sleep(mt_rand(1, 5));
            }
        }
        return false;
    }

    /**
     * Löscht eine Semaphore.
     *
     * @param string $ident Ein String der den Lock bezeichnet.
     */
    private function unlock(int $ident): void
    {
        IPS_SemaphoreLeave('ModBus.' . (string) $ident);
    }
}

/* @} */
