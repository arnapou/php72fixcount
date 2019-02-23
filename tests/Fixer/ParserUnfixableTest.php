<?php

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\Parser;

class ParserUnfixableTest extends \PHPUnit\Framework\TestCase
{
    public function testNoCount()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/NoCount.php');

        $this->assertSame([], $parser->getConflicts(), 'conflict');
        $this->assertSame([], $parser->getFixable(), 'fixable');
        $this->assertSame([], $parser->getUnfixable(), 'unfixable');
    }

    public function testNoNamespace()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/NoNamespace.php');

        $this->assertSame([], $parser->getConflicts(), 'conflict');
        $this->assertSame([], $parser->getFixable(), 'fixable');
        $this->assertSame([], $parser->getUnfixable(), 'unfixable');
    }
}
