<?php

namespace Arnapou\Php72FixCount\Tests;

class CountableObject implements \Countable
{
    public function count()
    {
        return 9;
    }
}
