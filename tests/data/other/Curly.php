<?php

namespace FixCount\Test\Curly;

class Curly
{
    public static $ale = 'ipa';
    public $type = 'ipa';

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

    public function mixed1()
    {
        echo "I'd like an {${self::$ale}}";
    }

    public function mixed2()
    {
        $var = 'type';
        echo "I'd like an {$this->{$var}}";
    }

}
