<?php

namespace MAKS\AmqpAgent\Tests\Helper;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Helper\IDGenerator;

class IDGeneratorTest extends TestCase
{
    public function testGenerateHashGeneratesUniqueHashes()
    {
        // Anything bigger than 1000000 will result in long execution times.
        // The methods is tested with up to 1000000, using 100 here to allow for faster testing.
        $array = [];
        for ($i = 0; $i < 100; $i) {
            $array[] = IDGenerator::generateHash();
            $i++;
        }

        $unique = count($array) === count(array_unique($array));

        $this->assertTrue($unique);
    }

    public function testGenerateHashWithAditionalParametersGeneratesUniqueHashes()
    {
        // Anything bigger than 1000000 will result in long execution times.
        // The methods is tested with up to 1000000, using 100 here to allow for faster testing.
        $array = [];
        for ($i = 0; $i < 100; $i) {
            $an = str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $array[] = IDGenerator::generateHash($an);
            $i++;
        }

        $unique = count($array) === count(array_unique($array));

        $this->assertTrue($unique);
    }

    public function testGenerateTokenGeneratesUniqueTokens()
    {
        // Anything bigger than 500000 will result in a very long execution times.
        // The methods is tested with up to 500000, using 100 here to allow for faster testing.
        $array = [];
        for ($i = 0; $i < 100; $i) {
            $array[] = IDGenerator::generateToken();
            $i++;
        }

        $unique = count($array) === count(array_unique($array));

        $this->assertTrue($unique);
    }

    public function testGenerateTokenWithAditionalParametersGeneratesUniqueTokens()
    {
        // Anything bigger than 500000 will result in a very long execution times.
        // The methods is tested with up to 500000, using 100 here to allow for faster testing.
        $array = [];
        for ($i = 0; $i < 100; $i) {
            $array[] = IDGenerator::generateToken(16, null, 'md5');
            $i++;
        }

        $unique = count($array) === count(array_unique($array));

        $this->assertTrue($unique);

        $this->assertEquals('a', IDGenerator::generateToken(1, 'a', null));
    }
}
