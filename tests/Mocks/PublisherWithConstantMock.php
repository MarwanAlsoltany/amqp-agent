<?php

namespace MAKS\AmqpAgent\Tests\Mocks;

use MAKS\AmqpAgent\Worker\Publisher;

class PublisherWithConstantMock extends Publisher
{
    // PublisherWithConstantMock

    public const TEST_CONSTANT = 'TEST';
}
