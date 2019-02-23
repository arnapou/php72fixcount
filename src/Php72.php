<?php

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
