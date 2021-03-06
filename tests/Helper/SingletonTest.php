<?php

namespace MAKS\AmqpAgent\Tests\Helper;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Tests\Mocks\SingletonMock;
use MAKS\AmqpAgent\Exception\SingletonViolationException;

class SingletonTest extends TestCase
{
    public function testSingleton()
    {
        $singleton1 = SingletonMock::getInstance();
        $singleton2 = SingletonMock::getInstance();
        $this->assertEquals($singleton2, $singleton1);
    }

    public function testDestroySingleton()
    {
        $singleton = SingletonMock::getInstance();
        $singleton = $singleton->destroyInstance($singleton);
        $this->assertEmpty($singleton);
    }

    public function testSingletonExceptionIsRaisedWhenTryingToCloneTheSingleton()
    {
        $this->expectException(SingletonViolationException::class);
        $singleton = SingletonMock::getInstance();
        $error = clone $singleton;
    }

    public function testSingletonExceptionIsRaisedWhenTryingToSerializeTheSingleton()
    {
        $this->expectException(SingletonViolationException::class);
        $singleton = SingletonMock::getInstance();
        $error = serialize($singleton);
    }

    public function testSingletonExceptionIsRaisedWhenTryingToUnerializeTheSingleton()
    {
        $this->expectException(SingletonViolationException::class);
        $singleton = 'O:40:"MAKS\\AmqpAgent\\Tests\\Mocks\\SingletonMock":0:{}';
        $error = unserialize($singleton);
    }
}
