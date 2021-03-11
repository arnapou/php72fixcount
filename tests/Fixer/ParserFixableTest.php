<?php

/*
 * This file is part of the Arnapou Php72FixCount package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arnapou\Php72FixCount\Tests\Fixer;

use Arnapou\Php72FixCount\Fixer\Parser;
use Arnapou\Php72FixCount\Tests\TestCase;

class ParserFixableTest extends TestCase
{
    public function testNormalClass()
    {
        $parser = new Parser(__DIR__ . '/../data/fixable/NormalClass.php');

        self::assertSame([], $parser->getConflicts('count'), 'conflict');
        self::assertSame(['FixCount\\Test\\NormalClass' => 2], $parser->getFixable('count'), 'fixable');
        self::assertSame(['FixCount\\Test\\NormalClass' => 3], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testNormalTrait()
    {
        $parser = new Parser(__DIR__ . '/../data/fixable/NormalTrait.php');

        self::assertSame([], $parser->getConflicts('count'), 'conflict');
        self::assertSame(['FixCount\\Test\\NormalTrait' => 1], $parser->getFixable('count'), 'fixable');
        self::assertSame([], $parser->getUnfixable('count'), 'unfixable');
    }

    public function testMultipleNamespace()
    {
        $parser = new Parser(__DIR__ . '/../data/fixable/MultipleNamespace.php');

        self::assertSame([], $parser->getConflicts('count'), 'conflict');
        self::assertSame(['FixCount\\Test\\Namespace1' => 2, 'FixCount\\Test\\Namespace2' => 2], $parser->getFixable('count'), 'fixable');
        self::assertSame(['FixCount\\Test\\Namespace1' => 3, 'FixCount\\Test\\Namespace2' => 3], $parser->getUnfixable('count'), 'unfixable');
    }
}
