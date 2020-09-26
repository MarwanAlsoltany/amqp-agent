<?php

namespace MAKS\AmqpAgent\Tests\Mocks;

use MAKS\AmqpAgent\Config\AbstractParameters;

final class AbstractParametersMock extends AbstractParameters
{
    // AbstractParametersMock

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
