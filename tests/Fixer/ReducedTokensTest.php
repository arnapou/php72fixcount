<?php

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\ReducedTokens;
use Arnapou\Php72FixCount\Tests\TestCase;

class ReducedTokensTest extends TestCase
{
    /**
     * @param string $filename
     * @return array
     */
    protected function reduceTokens($filename)
    {
        $reducedTokens = new ReducedTokens(token_get_all(file_get_contents($filename)));
        return iterator_to_array($reducedTokens);
    }

    public function testSkippedInterface()
    {
        $this->assertSame(
            [
                [ReducedTokens::T_NAMESPACE, 'FixCount\Test\NoCountInInterface'],
                [ReducedTokens::T_FUNCTION_CALL, 'end_of_file'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/unfixable/NoCountInInterface.php')
        );
    }

    public function testNoNamespace()
    {
        $this->assertSame(
            [],
            $this->reduceTokens(__DIR__ . '/../data/unfixable/NoNamespace.php')
        );
    }

    public function testNoCount()
    {
        $this->assertSame(
            [
                [ReducedTokens::T_NAMESPACE, 'FixCount\Test\NoCount'],
                [ReducedTokens::T_CLASS, 'NoCount'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION, 'test'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'count'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/unfixable/NoCount.php')
        );
    }

    public function testNormalTrait()
    {
        $this->assertSame(
            [
                [ReducedTokens::T_NAMESPACE, 'FixCount\Test\NormalTrait'],
                [ReducedTokens::T_TRAIT, 'NormalTrait'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION, 'test1'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, 'count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/fixable/NormalTrait.php')
        );
    }

    public function testMultipleNamespace()
    {
        $this->assertSame(
            [
                [ReducedTokens::T_NAMESPACE, 'FixCount\Test\Namespace1'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_CLASS, 'MultipleNamespace'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION, 'test1'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, 'count'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'test2'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, 'count'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'test3'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_FUNCTION_CALL, '\another\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_NAMESPACE, 'FixCount\Test\Namespace2'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_CLASS, 'MultipleNamespace'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION, 'test1'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, 'count'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'test2'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, 'count'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'test3'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_FUNCTION_CALL, '\another\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/fixable/MultipleNamespace.php')
        );
    }

    public function testUseFunction()
    {
        $this->assertSame(
            [
                [ReducedTokens::T_NAMESPACE, 'FixCount\Test\UseFunctionAlias'],
                [ReducedTokens::T_USE_FUNCTION, ['Another\HackedCount', 'count']],
                [ReducedTokens::T_CLASS, 'UseFunctionAlias'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION, 'test1'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, 'count'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'test2'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, 'count'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'test3'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION_CALL, '\count'],
                [ReducedTokens::T_FUNCTION_CALL, '\another\count'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/conflicts/UseFunctionAlias.php')
        );
    }

    public function testCurly()
    {
        $this->assertSame(
            [
                [ReducedTokens::T_NAMESPACE, 'FixCount\Test\Curly'],
                [ReducedTokens::T_CLASS, 'Curly'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_FUNCTION, 'T_CURLY_OPEN'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'T_DOLLAR_OPEN_CURLY_BRACES'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'T_STRING_VARNAME'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'mixed1'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_FUNCTION, 'mixed2'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_OPEN, '{'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
                [ReducedTokens::T_BRACE_CLOSE, '}'],
            ],
            $this->reduceTokens(__DIR__ . '/../data/other/Curly.php')
        );
    }
}
