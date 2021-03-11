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

class ParserUnfixableTest extends TestCase
{
    public function testNoCount()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/NoCount.php');

        self::assertSame([], $parser->getConflicts('count'), 'conflict');
        self::assertSame([], $parser->getFixable('count'), 'fixable');
        self::assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testInterface()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/NoCountInInterface.php');

        self::assertSame([], $parser->getConflicts('count'), 'conflict');
        self::assertSame([], $parser->getFixable('count'), 'fixable');
        self::assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testNoNamespace()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/NoNamespace.php');

        self::assertSame([], $parser->getConflicts('count'), 'conflict');
        self::assertSame([], $parser->getFixable('count'), 'fixable');
        self::assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testStaticCountFunction()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/StaticCountFunction.php');

        self::assertSame([], $parser->getConflicts('count'), 'conflict');
        self::assertSame([], $parser->getFixable('count'), 'fixable');
        self::assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testUseNativeCount()
    {
        $parser = new Parser(__DIR__ . '/../data/unfixable/UseNativeCount.php');

        self::assertSame([], $parser->getConflicts('count'), 'conflict');
        self::assertSame([], $parser->getFixable('count'), 'fixable');
        self::assertSame(['FixCount\Test\UseNativeCount' => 1], $parser->getUnfixable('count'), 'unfixable');
    }
}
