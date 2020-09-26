<?php

namespace MAKS\AmqpAgent\Test;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Exception\AmqpAgentException;
use MAKS\AmqpAgent\Exception\ConstantDoesNotExistException;

class AmqpAgentExceptionTest extends TestCase
{
    /**
     * @var AmqpAgentException
     */
    private $exception;

    public function setUp(): void
    {
        parent::setUp();
        $this->exception = new AmqpAgentException("This's a test!");
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->exception);
    }

    public function testAmqpAgentExceptionCanBeCastedToString()
    {
        $exception = (string)$this->exception;
        $this->assertStringContainsString(AmqpAgentException::class, $exception);
        $this->assertStringContainsString("This's a test!", $exception);
    }

    public function testAmqpAgentExceptionRethrow()
    {
        $this->expectException(AmqpAgentException::class);
        AmqpAgentException::rethrow($this->exception);
    }

    public function testAmqpAgentExceptionRethrowViaAliasMethod()
    {
        $this->expectException(AmqpAgentException::class);
        AmqpAgentException::rethrowException($this->exception);
    }

    public function testAmqpAgentExceptionRethrowWithMessageParameter()
    {
        try {
            AmqpAgentException::rethrow($this->exception, 'Test Prefix');
        } catch (\Exception $e) {
            $this->assertStringContainsString("Test Prefix", $e->getMessage());
            $this->expectException(AmqpAgentException::class);
            throw $e;
        }
    }

    public function testAmqpAgentExceptionRethrowStringWrapParameter()
    {
        $this->expectException(ConstantDoesNotExistException::class);
        AmqpAgentException::rethrow($this->exception, null, ConstantDoesNotExistException::class);
    }

    public function testAmqpAgentExceptionRethrowBooleanWrapParameter()
    {
        $this->expectException(AmqpAgentException::class);
        AmqpAgentException::rethrow($this->exception, null, true);
    }
}
