<?php

namespace Arnapou\Php72FixCount\Tests\Php72;

use Arnapou\Php72FixCount\Php72;
use Arnapou\Php72FixCount\Tests\CountableObject;
use Arnapou\Php72FixCount\Tests\TestCase;

class Php72Test extends TestCase
{
    public function testCountString()
    {
        $this->assertSame(1, Php72::count(''), "count('')");
        $this->assertSame(1, Php72::count('abc'), "count('abc')");
    }

    public function testCountIntegerAsString()
    {
        $this->assertSame(1, Php72::count('0'), "count('0')");
        $this->assertSame(1, Php72::count('42'), "count('42')");
    }

    public function testCountInteger()
    {
        $this->assertSame(1, Php72::count(0), 'count(0)');
        $this->assertSame(1, Php72::count(42), 'count(42)');
    }

    public function testCountFloat()
    {
        $this->assertSame(1, Php72::count(0.0), 'count(0.0)');
        $this->assertSame(1, Php72::count(7.2), 'count(7.2)');
    }

    public function testCountBoolean()
    {
        $this->assertSame(1, Php72::count(false), 'count(false)');
        $this->assertSame(1, Php72::count(true), 'count(true)');
    }

    public function testCountArray()
    {
        $this->assertSame(0, Php72::count([]), 'count([])');
        $this->assertSame(3, Php72::count([1, 2, 3]), 'count([1, 2, 3])');
    }

    public function testCountNull()
    {
        $this->assertSame(0, Php72::count(null), 'count(null)');
    }

    public function testCountObject()
    {
        $this->assertSame(1, Php72::count(new \stdClass()), "count(new \stdClass())");
        $this->assertSame(9, Php72::count(new CountableObject()), 'count(new CountableObject())');
    }
}
