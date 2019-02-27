<?php

/*
 * This file is part of the Arnapou Php71FixCount package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arnapou\Php72FixCount;

final class Php72
{
    public static function count($item, $mode = COUNT_NORMAL)
    {
        if (\is_array($item) || $item instanceof \Countable) {
            return \count($item, $mode);
        }
        return $item === null ? 0 : 1;
    }
}
