<?php

namespace MAKS\AmqpAgent;

use PHPUnit\Framework\TestCase as PHPUnit;

class TestCase extends PHPUnit
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
