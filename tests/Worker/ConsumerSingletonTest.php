<?php

namespace MAKS\AmqpAgent\Test\Worker;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Worker\ConsumerSingleton;

class ConsumerSingletonTest extends TestCase
{
    public function testSingleton()
    {
        $consumer1 = ConsumerSingleton::getInstance();
        $consumer2 = ConsumerSingleton::getInstance();
        $this->assertEquals($consumer2, $consumer1);
    }
}
