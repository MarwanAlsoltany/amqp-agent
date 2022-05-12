<?php

namespace MAKS\AmqpAgent\Tests\Helper;

use PHPUnit\Framework\Error;
use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Helper\Utility;

class UtilityTest extends TestCase
{
    public function testTimeWithAllParameters()
    {
        $date = Utility::time('07/12/1997', 'UTC');
        $this->assertEquals(868665600, $date->getTimestamp());
    }

    public function testEmitMethodWhenNothing()
    {
        $this->expectException(Error\Error::class);
        $emit = Utility::emit(null, null, E_USER_ERROR);
        $this->assertTrue($emit);
    }

    public function testEmitMethodWhenPassingString()
    {
        $this->expectException(Error\Warning::class);
        $emit = Utility::emit('Test Notice!', 'red', E_USER_WARNING);
        $this->assertTrue($emit);
    }

    public function testEmitMethodWhenPassingArray()
    {
        $this->expectException(Error\Notice::class);
        $emit = Utility::emit(['yellow' => 'Test','green' => 'Notice', '!'], 'white', E_USER_NOTICE);
        $this->assertTrue($emit);
    }

    public function testBacktrackPluckAsString()
    {
        $backtrace = Utility::backtrace('class');
        $this->assertTrue(
            // windows and linux have different results
            in_array($backtrace, ['PHPUnit\TextUI\Command', null])
        );
    }

    public function testBacktrackPluckAsArray()
    {
        $backtrace = Utility::backtrace(['class', 'function']);
        $this->assertIsArray($backtrace);
        $this->assertTrue(
            // windows and linux have different results
            in_array($backtrace['class'] ?? null, ['PHPUnit\TextUI\Command', null])
        );
        $this->assertTrue(
            // windows and linux have different results
            in_array($backtrace['function'] ?? null, ['main', null])
        );
    }

    public function testBacktrackReturnsNullOnOutOfBoundsOffset()
    {
        $backtrace = Utility::backtrace('function', 999999);
        $this->assertNull($backtrace);
    }

    public function testExecuteWithDiffrentParametersCombinations()
    {
        $this->assertNull(Utility::execute('php -v', null, true));
        $this->assertEquals(phpversion(), Utility::execute('php -r "echo phpversion();"', __DIR__, false));
    }

    public function testExecuteRaisesExceptionWhenProvidedWithEmptyCommand()
    {
        $this->expectException(\Exception::class);
        $this->assertNull(Utility::execute(''));
    }
}
