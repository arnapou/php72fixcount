<?php

namespace FixCount\Test\NormalTrait;

trait NormalTrait
{
    public function test1()
    {
        echo count([1, 2, 3]);
    }

}
