<?php

namespace FixCount\Test\StaticCountFunction;

class StaticCountFunction
{
    public function test()
    {
        echo "Hello World\n";
        echo self::count("abcde");
    }

    static public function count($var)
    {
        return \strlen($var);
    }

}
