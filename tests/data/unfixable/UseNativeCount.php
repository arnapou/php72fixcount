<?php

namespace FixCount\Test\UseNativeCount;

use function count;

class UseNativeCount
{
    public function test()
    {
        echo count([1, 2, 3]);
    }

}
