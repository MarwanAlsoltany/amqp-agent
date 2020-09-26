<?php

namespace MAKS\AmqpAgent\Test\Config;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Config\AbstractParameters;
use MAKS\AmqpAgent\Exception\ConstantDoesNotExistException;

class AbstractParametersMock extends AbstractParameters {
    public const TEST_CONST = [
        'library' => 'amqp-agent',
        'author' => 'Marwan Al-Soltany',
        'stable' => true,
        'rank' => 999999,
        'keywords' => [
            'rabbitmq',
            'php-amqplib'
        ]
    ];
}

class AbstractParametersTest extends TestCase
{
    public function testPatchPatchesAnArray()
    {
        $original = AbstractParametersMock::TEST_CONST;
        $overrides = [
            'rank' => 1,
            'extra' => rand()
        ];
        $patched = AbstractParametersMock::patch($overrides, 'TEST_CONST');
        $this->assertEquals(1, $patched['rank']);
        $this->assertArrayHasKey('author', $patched);
        $this->assertArrayNotHasKey('extra', $patched);
        unset($original['rank']);
        unset($patched['rank']);
        $this->assertEqualsCanonicalizing($original, $patched);
    }

    public function testPatchThrowsAnExceptionForNoneExistingConstants()
    {
        $this->expectException(ConstantDoesNotExistException::class);
        AbstractParametersMock::patch([], 'UNKNOWN_CONST');
    }
}
