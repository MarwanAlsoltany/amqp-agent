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

    public function testObjectToArrayMethodReturnsExpectedValue()
    {
        $object = new \stdClass();
        $object->prop1 = 'test';
        $object->prop2 = 123;
        $object->prop3 = [null, false, true, 0, 1, 2, 3, 'string'];
        $object->propsCount = 3;

        $array = Utility::objectToArray($object, false);

        $this->assertIsArray($array);
        $this->assertArrayHasKey('prop1', $array);
        $this->assertEquals($object->prop2, $array['prop2']);
        $this->assertIsArray($array['prop3']);
    }

    public function testObjectToArrayMethodUsingJsonReturnsExpectedValue()
    {
        $object = new \stdClass();
        $object->prop1 = 'test';
        $object->prop2 = 123;
        $object->prop3 = [null, false, true, 0, 1, 2, 3, 'string'];
        $object->propsCount = 3;

        $array = Utility::objectToArray($object, true);

        $this->assertIsArray($array);
        $this->assertArrayHasKey('prop1', $array);
        $this->assertEquals($object->prop2, $array['prop2']);
        $this->assertIsArray($array['prop3']);
    }

    public function testArrayToObjectMethodReturnsExpectedValue()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => [null, false, true, 0, 1, 2, 3, 'string'],
            'propsCount' => 3,
        ];

        $object = Utility::arrayToObject($array, false);

        $this->assertIsObject($object);
        $this->assertObjectHasAttribute('prop1', $object);
        $this->assertEquals($array['prop2'], $object->prop2);
        $this->assertIsObject($object->prop3);
    }

    public function testArrayToObjectMethodUsingJsonReturnsExpectedValue()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => [null, false, true, 0, 1, 2, 3, 'string'],
            'propsCount' => 3,
        ];

        $object = Utility::arrayToObject($array, true);

        $this->assertIsObject($object);
        $this->assertObjectHasAttribute('prop1', $object);
        $this->assertEquals($array['prop2'], $object->prop2);
        $this->assertIsArray($object->prop3);
    }

    public function testGetArrayValueByKeyReturnsExpectedValues()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => ['sub1' => true, 'sub2' => false, 'sub3' => ['sup' => null]]
        ];

        $this->assertEquals('fallback', Utility::getArrayValueByKey($array, 'not.found', 'fallback'));
        $this->assertEquals('none', Utility::getArrayValueByKey($array, 'prop0', 'none'));
        $this->assertEquals('test', Utility::getArrayValueByKey($array, 'prop1'));
        $this->assertEquals(123, Utility::getArrayValueByKey($array, 'prop2'));
        $this->assertTrue(Utility::getArrayValueByKey($array, 'prop3.sub1'));
        $this->assertFalse(Utility::getArrayValueByKey($array, 'prop3.sub2'));
        $this->assertNull(Utility::getArrayValueByKey($array, 'prop3.sub3.sup'));

        $arr = [];
        $str = '';
        $this->assertEquals($str, Utility::getArrayValueByKey($arr, $str, $str));
    }

    public function testSetArrayValueByKeyReturnsExpectedValues()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => ['sub1' => true, 'sub2' => ['sup' => null]]
        ];

        Utility::setArrayValueByKey($array, 'prop4', 'abc');
        Utility::setArrayValueByKey($array, 'prop5.sub1.sup', 'xyz');

        $this->assertArrayHasKey('prop4', $array);
        $this->assertArrayHasKey('sup', $array['prop5']['sub1']);
        $this->assertEquals('abc', $array['prop4']);
        $this->assertEquals('xyz', $array['prop5']['sub1']['sup']);

        $arr = [];
        $str = '';
        $this->assertFalse(Utility::setArrayValueByKey($arr, $str, $str));
    }
}
