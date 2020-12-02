<?php

namespace MAKS\AmqpAgent\Tests\Helper;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Tests\Mocks\PropertiesMock;
use MAKS\AmqpAgent\Tests\Mocks\MethodsMock;
use MAKS\AmqpAgent\Helper\ClassProxy;
use MAKS\AmqpAgent\Worker;
use MAKS\AmqpAgent\Exception\AmqpAgentException;

class ClassProxyTest extends TestCase
{
    public function testCallMethodMethod()
    {
        $class = new MethodsMock();

        $this->assertEquals('PRIVATE: is not so private!', ClassProxy::callMethod($class, 'privateMethod', 'is not so private!'));
        $this->assertEquals('PROTECTED: is not so protected!', ClassProxy::callMethod($class, 'protectedMethod', 'is not so protected!'));
        $this->assertEquals('PUBLIC: is simply public!', ClassProxy::callMethod($class, 'publicMethod', 'is simply public!'));

        $this->expectException(AmqpAgentException::class);
        ClassProxy::call($class, 'exception');
    }

    public function testGetPropertyMethod()
    {
        $class = new PropertiesMock();

        $this->assertEquals('PRIVATE', ClassProxy::getProperty($class, 'privateProp'));
        $this->assertEquals('PROTECTED', ClassProxy::getProperty($class, 'protectedProp'));
        $this->assertEquals('PUBLIC', ClassProxy::getProperty($class, 'publicProp'));
        $this->assertEquals('STATIC', ClassProxy::getProperty($class, 'staticProp'));
        $this->assertEquals('CONST', ClassProxy::getProperty($class, 'CONST_PROP'));

        $this->expectException(AmqpAgentException::class);
        ClassProxy::get($class, 'UNKNOWN');
    }

    public function testSetPropertyMethod()
    {
        $class = new PropertiesMock();

        $this->assertEquals('private', ClassProxy::setProperty($class, 'privateProp', 'private'));
        $this->assertEquals('protected', ClassProxy::setProperty($class, 'protectedProp', 'protected'));
        $this->assertEquals('public', ClassProxy::setProperty($class, 'publicProp', 'public'));
        $this->assertEquals('static', ClassProxy::setProperty($class, 'staticProp', 'static'));

        $this->expectException(AmqpAgentException::class);
        ClassProxy::set($class, 'UNKNOWN', 'UNKNOWN');
    }

    public function testReflectOnClassMethod()
    {
        $reflection = ClassProxy::reflectOnClass(\Exception::class);
        $this->assertInstanceOf(\ReflectionClass::class, $reflection);
    }

    public function testReflectOnObjectMethod()
    {
        $reflection = ClassProxy::reflectOnObject(new \stdClass);
        $this->assertInstanceOf(\ReflectionObject::class, $reflection);
    }

    public function testCastObjectToClassMethodRaisesAnExceptionWhenProvidedWithAClassThatDoesNotExist()
    {
        $this->expectException(AmqpAgentException::class);
        ClassProxy::castObjectToClass(new \stdClass, 'BlaBla');
    }

    public function testCastObjectToClassMethodWithAWrongArgument()
    {
        $this->expectException(AmqpAgentException::class);
        ClassProxy::castObjectToClass([], 'Error');
    }

    public function testCastObjectToClassMethodWithAClassWithPrivateProperties()
    {
        $class = new class {
            public static $staticProp = 'P0';
            public static $staticPropX = 'P0A';
            private $privateProp = 'P1';
            private $privatePropX = 'P1A';
            protected $protectedProp = 'P2';
            protected $protectedPropX = 'P2A';
            public $publicProp = 'P3';
            public $publicPropX = 'P3A';
        };

        $object = ClassProxy::castObjectToClass($class, PropertiesMock::class);

        $this->assertObjectHasAttribute('privateProp', $object);
        $this->assertObjectHasAttribute('protectedProp', $object);
        $this->assertObjectHasAttribute('publicProp', $object);
        $this->assertEquals('P0', $object::$staticProp);
        $this->assertObjectHasAttribute('publicProp', $object);
        $this->assertObjectNotHasAttribute('publicPropX', $object);
    }
}
