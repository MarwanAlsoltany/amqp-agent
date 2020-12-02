<?php

namespace MAKS\AmqpAgent\Tests\Mocks;

use MAKS\AmqpAgent\RPC\AbstractEndpoint;

final class AbstractEndpointMock extends AbstractEndpoint
{
    // AbstractEndpointMock

    protected function callback(\PhpAmqpLib\Message\AMQPMessage $m): string
    {
        return '';
    }
}
