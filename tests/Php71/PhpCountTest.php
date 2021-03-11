<?php

/*
 * This file is part of the Arnapou Php71FixCount package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arnapou\Php72FixCount\Tests\Php71;

use Arnapou\Php72FixCount\Tests\CountableObject;
use Arnapou\Php72FixCount\Tests\TestCase;

class PhpCountTest extends TestCase
{
    public function testCountString()
    {
        self::assertSame(1, \count(''), "count('')");
        self::assertSame(1, \count('abc'), "count('abc')");
    }

    public function testCountIntegerAsString()
    {
        self::assertSame(1, \count('0'), "count('0')");
        self::assertSame(1, \count('42'), "count('42')");
    }

    public function testCountInteger()
    {
        self::assertSame(1, \count(0), 'count(0)');
        self::assertSame(1, \count(42), 'count(42)');
    }

    public function testCountFloat()
    {
        self::assertSame(1, \count(0.0), 'count(0.0)');
        self::assertSame(1, \count(7.2), 'count(7.2)');
    }

    public function testCountBoolean()
    {
        self::assertSame(1, \count(false), 'count(false)');
        self::assertSame(1, \count(true), 'count(true)');
    }

    public function testCountArray()
    {
        self::assertSame(0, \count([]), 'count([])');
        self::assertSame(3, \count([1, 2, 3]), 'count([1, 2, 3])');
    }

    public function testCountNull()
    {
        self::assertSame(0, \count(null), 'count(null)');
    }

    public function testCountObject()
    {
        self::assertSame(1, \count(new \stdClass()), "count(new \stdClass())");
        self::assertSame(9, \count(new CountableObject()), 'count(new CountableObject())');
    }
}
