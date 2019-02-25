<?php

namespace FixCount\Test\Curly;

class Curly
{
    public function T_CURLY_OPEN()
    {
        $great = 'fantastic';
        echo "This is {$great}";
    }

    public function T_DOLLAR_OPEN_CURLY_BRACES()
    {
        $great = 'fantastic';
        echo "This is " . ${'great'};
    }

    public function T_STRING_VARNAME()
    {
        $great = 'fantastic';
        echo "This is ${great}";
    }

}
