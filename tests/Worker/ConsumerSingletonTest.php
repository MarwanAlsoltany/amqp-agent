<?php

namespace MAKS\AmqpAgent\Tests\Worker;

use MAKS\AmqpAgent\Tests\TestCase;
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
