<?php

namespace Arnapou\Php72FixCount\Tests\Php71;

use Arnapou\Php72FixCount\Tests\CountableObject;
use Arnapou\Php72FixCount\Tests\TestCase;

class PhpCountTest extends TestCase
{
    public function testCountString()
    {
        $this->assertSame(1, \count(''), "count('')");
        $this->assertSame(1, \count('abc'), "count('abc')");
    }

    public function testCountIntegerAsString()
    {
        $this->assertSame(1, \count('0'), "count('0')");
        $this->assertSame(1, \count('42'), "count('42')");
    }

    public function testCountInteger()
    {
        $this->assertSame(1, \count(0), 'count(0)');
        $this->assertSame(1, \count(42), 'count(42)');
    }

    public function testCountFloat()
    {
        $this->assertSame(1, \count(0.0), 'count(0.0)');
        $this->assertSame(1, \count(7.2), 'count(7.2)');
    }

    public function testCountBoolean()
    {
        $this->assertSame(1, \count(false), 'count(false)');
        $this->assertSame(1, \count(true), 'count(true)');
    }

    public function testCountArray()
    {
        $this->assertSame(0, \count([]), 'count([])');
        $this->assertSame(3, \count([1, 2, 3]), 'count([1, 2, 3])');
    }

    public function testCountNull()
    {
        $this->assertSame(0, \count(null), 'count(null)');
    }

    public function testCountObject()
    {
        $this->assertSame(1, \count(new \stdClass()), "count(new \stdClass())");
        $this->assertSame(9, \count(new CountableObject()), 'count(new CountableObject())');
    }
}
