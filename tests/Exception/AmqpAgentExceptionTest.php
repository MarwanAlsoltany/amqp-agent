<?php

namespace MAKS\AmqpAgent\Test;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Exception\AmqpAgentException;

class AmqpAgentExceptionTest extends TestCase
{
    public function testAmqpAgentExceptionCanBeCastedToString()
    {
        $exception = (string) new AmqpAgentException("This's a test!");
        $this->assertStringContainsString(AmqpAgentException::class, $exception);
        $this->assertStringContainsString("This's a test!", $exception);
    }

    public function testAmqpAgentExceptionRethrow()
    {
        $exception = new AmqpAgentException("This's a test!");
        $this->expectException(AmqpAgentException::class);
        AmqpAgentException::rethrowException($exception);
    }
}
