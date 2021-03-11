<?php

namespace FixCount\Test\Relative {

    class ThisIsARareCase
    {

    }
}

namespace FixCount\Test {

    $v = new namespace\Relative\ThisIsARareCase;

    echo namespace\Relative\ThisIsARareCase::class;
}
