<?php

namespace FixCount\Test\UseFunction;

class ConflictInAnotherClassOfThisNamespace
{
    public function test1()
    {
        echo count([1, 2, 3]);
    }

    public function test2()
    {
        echo \count([1, 2, 3]);
    }

    public function test3()
    {
        echo \another\count([1, 2, 3]);
    }
}
