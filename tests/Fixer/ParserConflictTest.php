<?php

/*
 * This file is part of the Arnapou Php71FixCount package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\Parser;
use Arnapou\Php72FixCount\Tests\TestCase;

class ParserConflictTest extends TestCase
{
    public function testUseFunction()
    {
        $parser = new Parser(__DIR__ . '/../data/conflicts/UseFunction.php');

        self::assertSame(['FixCount\\Test\\UseFunction' => 1], $parser->getConflicts('count'), 'conflict');
        self::assertSame(['FixCount\\Test\\UseFunction' => 2], $parser->getFixable('count'), 'fixable');
        self::assertSame(['FixCount\\Test\\UseFunction' => 3], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testUseFunctionAlias()
    {
        $parser = new Parser(__DIR__ . '/../data/conflicts/UseFunctionAlias.php');

        self::assertSame(['FixCount\\Test\\UseFunctionAlias' => 1], $parser->getConflicts('count'), 'conflict');
        self::assertSame(['FixCount\\Test\\UseFunctionAlias' => 2], $parser->getFixable('count'), 'fixable');
        self::assertSame(['FixCount\\Test\\UseFunctionAlias' => 3], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testCountRedefinedInNS()
    {
        $parser = new Parser(__DIR__ . '/../data/conflicts/CountRedefinedInNS.php');

        self::assertSame(['FixCount\\Test\\CountRedefinedInNS' => 1], $parser->getConflicts('count'), 'conflict');
        self::assertSame(['FixCount\\Test\\CountRedefinedInNS' => 2], $parser->getFixable('count'), 'fixable');
        self::assertSame(['FixCount\\Test\\CountRedefinedInNS' => 3], $parser->getUnfixable('count'), 'unfixable');
    }
}
