<?php

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\Parser;
use Arnapou\Php72FixCount\Tests\TestCase;

class ParserUnfixableTest extends TestCase
{
    public function testNoCount()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/NoCount.php');

        $this->assertSame([], $parser->getConflicts('count'), 'conflict');
        $this->assertSame([], $parser->getFixable('count'), 'fixable');
        $this->assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testInterface()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/NoCountInInterface.php');

        $this->assertSame([], $parser->getConflicts('count'), 'conflict');
        $this->assertSame([], $parser->getFixable('count'), 'fixable');
        $this->assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testNoNamespace()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/NoNamespace.php');

        $this->assertSame([], $parser->getConflicts('count'), 'conflict');
        $this->assertSame([], $parser->getFixable('count'), 'fixable');
        $this->assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testStaticCountFunction()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/StaticCountFunction.php');

        $this->assertSame([], $parser->getConflicts('count'), 'conflict');
        $this->assertSame([], $parser->getFixable('count'), 'fixable');
        $this->assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testUseNativeCount()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/UseNativeCount.php');

        $this->assertSame([], $parser->getConflicts('count'), 'conflict');
        $this->assertSame([], $parser->getFixable('count'), 'fixable');
        $this->assertSame(['FixCount\Test\UseNativeCount' => 1], $parser->getUnfixable('count'), 'unfixable');
    }
}
