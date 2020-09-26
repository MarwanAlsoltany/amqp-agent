<?php

namespace MAKS\AmqpAgent\Tests\Mocks;

use MAKS\AmqpAgent\Tests\Mocks\PublisherWithConstantMock;
use MAKS\AmqpAgent\Worker\AbstractWorkerSingleton;

final class PublisherSingletonWithConstantMock extends AbstractWorkerSingleton
{
    // PublisherSingletonWithConstantMock

    public function __construct()
    {
        $this->worker = new PublisherWithConstantMock();
    }
}
