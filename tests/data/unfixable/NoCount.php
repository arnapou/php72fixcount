<?php

namespace FixCount\Test\NoCount;

class NoCount extends AnyClass
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
