<?php

namespace MAKS\AmqpAgent\Tests\Helper;

use MAKS\AmqpAgent\Tests\TestCase;
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

    public function testCollapseWithAllPossibleDataTypes()
    {
        $array = [
            null,
            true,
            false,
            97,
            9.7,
            'string',
            ['a', 'b', 'c'],
            new \stdClass
        ];

        $string = Utility::collapse($array);

        $this->assertStringContainsString('null', $string);
        $this->assertStringContainsString('true', $string);
        $this->assertStringContainsString('false', $string);
        $this->assertStringContainsString(strval(97), $string);
        $this->assertStringContainsString(strval(9.7), $string);
        $this->assertStringContainsString("'string'", $string);
        $this->assertStringContainsString("['a', 'b', 'c']", $string);
        $this->assertStringContainsString('stdClass', $string);
    }
}
