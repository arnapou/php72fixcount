<?php

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\Parser;

class ParserFixableTest extends \PHPUnit\Framework\TestCase
{
    public function testNormalClass()
    {
        $parser = new Parser(__DIR__ . '/../data/fixable/NormalClass.php');

        $this->assertSame([], $parser->getConflicts(), 'conflict');
        $this->assertSame(['FixCount\\Test\\NormalClass' => 2], $parser->getFixable(), 'fixable');
        $this->assertSame(['FixCount\\Test\\NormalClass' => 3], $parser->getUnfixable(), 'unfixable');
    }

    public function testMultipleNamespace()
    {
        $parser = new Parser(__DIR__ . '/../data/fixable/MultipleNamespace.php');

        $this->assertSame([], $parser->getConflicts(), 'conflict');
        $this->assertSame(['FixCount\\Test\\Namespace1' => 2, 'FixCount\\Test\\Namespace2' => 2], $parser->getFixable(), 'fixable');
        $this->assertSame(['FixCount\\Test\\Namespace1' => 3, 'FixCount\\Test\\Namespace2' => 3], $parser->getUnfixable(), 'unfixable');
    }
}
