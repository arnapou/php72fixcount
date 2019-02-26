<?php

namespace FixCount\Test\NoCountInInterface;

interface NoCountInInterface
{
    public function test();

    public function count();

}

// function here only to detect correctly the end of file
// the test will fail if the brace block detection is bad
end_of_file();
