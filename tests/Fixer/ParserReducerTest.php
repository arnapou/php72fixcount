<?php

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\Parser;
use Arnapou\Php72FixCount\Tests\TestCase;

class ParserReducerTest extends TestCase
{
    /**
     * @param string $filename
     * @return array
     */
    protected function reduceTokens($filename)
    {
        $parser        = new Parser($filename);
        $generator     = $this->invokeMethod($parser, 'reducedTokens');
        $array_to_test = [];
        foreach ($generator as $type => $string) {
            // need to write keys in another sub array because we can have the same keys
            // along the parsing and we don't want to override them
            $array_to_test[] = [$type => $string];
        }
        return $array_to_test;
    }

    public function testSkippedInterface()
    {
        $this->assertSame(
            [
                [Parser::T_NAMESPACE => 'FixCount\Test\NoCountInInterface'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/unfixable/NoCountInInterface.php')
        );
    }

    public function testNoCount()
    {
        $this->assertSame(
            [
                [Parser::T_NAMESPACE => 'FixCount\Test\NoCount'],
                [Parser::T_CLASS => 'NoCount'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION => 'test'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'count'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/unfixable/NoCount.php')
        );
    }

    public function testNormalTrait()
    {
        $this->assertSame(
            [
                [Parser::T_NAMESPACE => 'FixCount\Test\NormalTrait'],
                [Parser::T_TRAIT => 'NormalTrait'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION => 'test1'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => 'count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/fixable/NormalTrait.php')
        );
    }

    public function testMultipleNamespace()
    {
        $this->assertSame(
            [
                [Parser::T_NAMESPACE => 'FixCount\Test\Namespace1'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_CLASS => 'MultipleNamespace'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION => 'test1'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => 'count'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'test2'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => 'count'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'test3'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_FUNCTION_CALL => '\another\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_NAMESPACE => 'FixCount\Test\Namespace2'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_CLASS => 'MultipleNamespace'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION => 'test1'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => 'count'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'test2'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => 'count'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'test3'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_FUNCTION_CALL => '\another\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/fixable/MultipleNamespace.php')
        );
    }

    public function testUseFunction()
    {
        $this->assertSame(
            [
                [Parser::T_NAMESPACE => 'FixCount\Test\UseFunctionAlias'],
                [Parser::T_USE_FUNCTION => ['Another\HackedCount', 'count']],
                [Parser::T_CLASS => 'UseFunctionAlias'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION => 'test1'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => 'count'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'test2'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => 'count'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'test3'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION_CALL => '\count'],
                [Parser::T_FUNCTION_CALL => '\another\count'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/conflicts/UseFunctionAlias.php')
        );
    }

    public function testCurly()
    {
        $this->assertSame(
            [
                [Parser::T_NAMESPACE => 'FixCount\Test\Curly'],
                [Parser::T_CLASS => 'Curly'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_FUNCTION => 'T_CURLY_OPEN'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'T_DOLLAR_OPEN_CURLY_BRACES'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_FUNCTION => 'T_STRING_VARNAME'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_BRACE_OPEN => '{'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
                [Parser::T_BRACE_CLOSE => '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/other/Curly.php')
        );
    }
}
