<?php

namespace MAKS\AmqpAgent\Test\Helper;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Helper\Utility;

class UtilityTest extends TestCase
{
    public function testTimeWithAllParameters()
    {
        $date = Utility::time('07/12/1997', 'UTC');
        $this->assertEquals(868665600, $date->getTimestamp());
    }

    public function testBacktrackPluckAsString()
    {
        $backtrace = Utility::backtrace('class');
        $this->assertEquals($backtrace, 'PHPUnit\TextUI\Command');
    }

    public function testBacktrackPluckAsArray()
    {
        $backtrace = Utility::backtrace(['class', 'function']);
        $this->assertIsArray($backtrace);
        $this->assertEquals($backtrace['class'], 'PHPUnit\TextUI\Command');
        $this->assertEquals($backtrace['function'], 'main');
    }

    public function testBacktrackReturnsNullOnOutOfBoundsOffset()
    {
        $backtrace = Utility::backtrace('function', 999999);
        $this->assertNull($backtrace);
    }
}
