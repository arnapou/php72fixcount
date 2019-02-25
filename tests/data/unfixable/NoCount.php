<?php

namespace FixCount\Test\NoCount;

class NoCount
{
    public function test()
    {
        echo $this->count() . "\n";
    }

    public function count()
    {
        return 42;
    }

}
