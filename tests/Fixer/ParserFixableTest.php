<?php

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\Parser;
use Arnapou\Php72FixCount\Tests\TestCase;

class ParserFixableTest extends TestCase
{
    public function testNormalClass()
    {
        $parser = new Parser(__DIR__ . '/../data/fixable/NormalClass.php');

        $this->assertSame([], $parser->getConflicts('count'), 'conflict');
        $this->assertSame(['FixCount\\Test\\NormalClass' => 2], $parser->getFixable('count'), 'fixable');
        $this->assertSame(['FixCount\\Test\\NormalClass' => 3], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testNormalTrait()
    {
        $parser = new Parser(__DIR__ . '/../data/fixable/NormalTrait.php');

        $this->assertSame([], $parser->getConflicts('count'), 'conflict');
        $this->assertSame(['FixCount\\Test\\NormalTrait' => 1], $parser->getFixable('count'), 'fixable');
        $this->assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testMultipleNamespace()
    {
        $parser = new Parser(__DIR__ . '/../data/fixable/MultipleNamespace.php');

        $this->assertSame([], $parser->getConflicts('count'), 'conflict');
        $this->assertSame(['FixCount\\Test\\Namespace1' => 2, 'FixCount\\Test\\Namespace2' => 2], $parser->getFixable('count'), 'fixable');
        $this->assertSame(['FixCount\\Test\\Namespace1' => 3, 'FixCount\\Test\\Namespace2' => 3], $parser->getUnfixable('count'), 'unfixable');
    }
}
