<?php

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\Parser;

class ParserConflictTest extends \PHPUnit\Framework\TestCase
{
    public function testUseFunction()
    {
        $parser = new Parser(__DIR__ . '/../data/conflicts/UseFunction.php');

        $this->assertSame(['FixCount\\Test\\UseFunction' => 1], $parser->getConflicts(), 'conflict');
        $this->assertSame(['FixCount\\Test\\UseFunction' => 2], $parser->getFixable(), 'fixable');
        $this->assertSame(['FixCount\\Test\\UseFunction' => 3], $parser->getUnfixable(), 'unfixable');
    }

    public function testUseFunctionAlias()
    {
        $parser = new Parser(__DIR__ . '/../data/conflicts/UseFunctionAlias.php');

        $this->assertSame(['FixCount\\Test\\UseFunctionAlias' => 1], $parser->getConflicts(), 'conflict');
        $this->assertSame(['FixCount\\Test\\UseFunctionAlias' => 2], $parser->getFixable(), 'fixable');
        $this->assertSame(['FixCount\\Test\\UseFunctionAlias' => 3], $parser->getUnfixable(), 'unfixable');
    }

    public function testCountRedefinedInNS()
    {
        $parser = new Parser(__DIR__ . '/../data/conflicts/CountRedefinedInNS.php');

        $this->assertSame(['FixCount\\Test\\CountRedefinedInNS' => 1], $parser->getConflicts(), 'conflict');
        $this->assertSame(['FixCount\\Test\\CountRedefinedInNS' => 2], $parser->getFixable(), 'fixable');
        $this->assertSame(['FixCount\\Test\\CountRedefinedInNS' => 3], $parser->getUnfixable(), 'unfixable');
    }
}
