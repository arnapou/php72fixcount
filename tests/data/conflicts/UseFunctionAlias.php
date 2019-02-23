<?php

namespace FixCount\Test\UseFunctionAlias;

use function Another\HackedCound as count;

class UseFunctionAlias
{
    public function test1()
    {
        echo count([1, 2, 3]);
        echo \count([1, 2, 3]);
    }

    public function test2()
    {
        echo count([1, 2, 3]);
        echo \count([1, 2, 3]);
    }

    public function test3()
    {
        echo \count([1, 2, 3]);
        echo \another\count([1, 2, 3]);
    }
}
