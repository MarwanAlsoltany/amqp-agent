<?php

namespace MAKS\AmqpAgent\Tests\Helper;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Helper\ArrayProxy;

class ArrayProxyTest extends TestCase
{
    public function testCastArrayToStringWithAllPossibleDataTypes()
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

        $string = ArrayProxy::castArrayToString($array);

        $this->assertStringContainsString('null', $string);
        $this->assertStringContainsString('true', $string);
        $this->assertStringContainsString('false', $string);
        $this->assertStringContainsString(strval(97), $string);
        $this->assertStringContainsString(strval(9.7), $string);
        $this->assertStringContainsString("'string'", $string);
        $this->assertStringContainsString("['a', 'b', 'c']", $string);
        $this->assertStringContainsString('stdClass', $string);
    }

    public function testCastObjectToArrayMethodReturnsExpectedValue()
    {
        $object = new \stdClass();
        $object->prop1 = 'test';
        $object->prop2 = 123;
        $object->prop3 = [null, false, true, 0, 1, 2, 3, 'string'];
        $object->propsCount = 3;

        $array = ArrayProxy::castObjectToArray($object, false);

        $this->assertIsArray($array);
        $this->assertArrayHasKey('prop1', $array);
        $this->assertEquals($object->prop2, $array['prop2']);
        $this->assertIsArray($array['prop3']);
    }

    public function testCastObjectToArrayMethodUsingJsonReturnsExpectedValue()
    {
        $object = new \stdClass();
        $object->prop1 = 'test';
        $object->prop2 = 123;
        $object->prop3 = [null, false, true, 0, 1, 2, 3, 'string'];
        $object->propsCount = 3;

        $array = ArrayProxy::castObjectToArray($object, true);

        $this->assertIsArray($array);
        $this->assertArrayHasKey('prop1', $array);
        $this->assertEquals($object->prop2, $array['prop2']);
        $this->assertIsArray($array['prop3']);
    }

    public function testCastArrayToObjectMethodReturnsExpectedValue()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => [null, false, true, 0, 1, 2, 3, 'string'],
            'propsCount' => 3,
        ];

        $object = ArrayProxy::castArrayToObject($array, false);

        $this->assertIsObject($object);
        $this->assertObjectHasAttribute('prop1', $object);
        $this->assertEquals($array['prop2'], $object->prop2);
        $this->assertIsObject($object->prop3);
    }

    public function testCastArrayToObjectMethodUsingJsonReturnsExpectedValue()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => [null, false, true, 0, 1, 2, 3, 'string'],
            'propsCount' => 3,
        ];

        $object = ArrayProxy::castArrayToObject($array, true);

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

        $this->assertEquals('fallback', ArrayProxy::getArrayValueByKey($array, 'not.found', 'fallback'));
        $this->assertEquals('none', ArrayProxy::getArrayValueByKey($array, 'prop0', 'none'));
        $this->assertEquals('test', ArrayProxy::getArrayValueByKey($array, 'prop1'));
        $this->assertEquals(123, ArrayProxy::getArrayValueByKey($array, 'prop2'));
        $this->assertTrue(ArrayProxy::getArrayValueByKey($array, 'prop3.sub1'));
        $this->assertFalse(ArrayProxy::getArrayValueByKey($array, 'prop3.sub2'));
        $this->assertNull(ArrayProxy::getArrayValueByKey($array, 'prop3.sub3.sup'));

        $arr = [];
        $str = '';
        $this->assertEquals($str, ArrayProxy::getArrayValueByKey($arr, $str, $str));
    }

    public function testSetArrayValueByKeyReturnsExpectedValues()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => ['sub1' => true, 'sub2' => ['sup' => null]]
        ];

        ArrayProxy::setArrayValueByKey($array, 'prop4', 'abc');
        ArrayProxy::setArrayValueByKey($array, 'prop5.sub1.sup', 'xyz');

        $this->assertArrayHasKey('prop4', $array);
        $this->assertArrayHasKey('sup', $array['prop5']['sub1']);
        $this->assertEquals('abc', $array['prop4']);
        $this->assertEquals('xyz', $array['prop5']['sub1']['sup']);

        $arr = [];
        $str = '';
        $this->assertFalse(ArrayProxy::setArrayValueByKey($arr, $str, $str));
    }
}
