<?php

namespace MAKS\AmqpAgent\Tests\Config;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Tests\Mocks\AbstractParametersMock;
use MAKS\AmqpAgent\Exception\ConstantDoesNotExistException;

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
