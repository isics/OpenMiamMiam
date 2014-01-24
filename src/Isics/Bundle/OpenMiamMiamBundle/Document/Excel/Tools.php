<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Document\Excel;

class Tools
{
    /**
     * Returns Excel column name representation (A, AB, AAB...) for column index
     *
     * @param int $number
     *
     * @return string
     */
    public static function getColumnNameForNumber($number)
    {
        if ((int)$number < 26) {
            return chr((int)$number + 65);
        }
        else {
            return static::getColumnNameForNumber(floor($number / 26) - 1).chr($number%26 + 65);
        }
    }
}