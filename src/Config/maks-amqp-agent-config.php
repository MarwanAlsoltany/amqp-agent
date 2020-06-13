<?php

return [
    // Global
    // Start of static/const class properties. specific to AMQP Agent.
    // Only for reference, modifing them won't change anything.
    'prefix' => 'maks.amqp.agent.', // defualt
    // If you want to modify command* use ClassName::$variableName.
    'commandPrefix' => '__COMMAND__',
    'commandSyntax' => [
        '__COMMAND__'    =>    [
            'ACTION'    =>    'OBJECT',
            'PARAMS'    =>    [
                'NAME'    =>    'VALUE'
            ]
        ]
    ],
    // End of static/const class properties. specific to AMQP Agent.

    // Global
    'connectionOptions' => [
        'host'                   =>    'localhost',
        'port'                   =>    5672,
        'user'                   =>    'guest',
        'password'               =>    'guest',
        'vhost'                  =>    '/',
        'insist'                 =>    false,
        'login_method'           =>    'AMQPLAIN',
        'login_response'         =>    null,
        'locale'                 =>    'en_US',
        'connection_timeout'     =>    120,
        'read_write_timeout'     =>    120,
        'context'                =>    null,
        'keepalive'              =>    true,
        'heartbeat'              =>    60,
        'channel_rpc_timeout'    =>    120,
        'ssl_protocol'           =>    null
    ],
    'channelOptions' => [
        'channel_id'    =>    null
    ],
    'queueOptions' => [
        'queue'          =>    'maks.amqp.agent.queue',
        'passive'        =>    false,
        'durable'        =>    true,
        'exclusive'      =>    false,
        'auto_delete'    =>    false,
        'nowait'         =>    false,
        'arguments'      =>    [],
        'ticket'         =>    null
    ],

    // Publisher
    'exchangeOptions' => [
        'exchange'       =>    'maks.amqp.agent.exchange',
        'type'           =>    'headers',
        'passive'        =>    false,
        'durable'        =>    true,
        'auto_delete'    =>    false,
        'internal'       =>    false,
        'nowait'         =>    false,
        'arguments'      =>    [],
        'ticket'         =>    null
    ],
    'bindOptions' => [
        'queue'          =>    'maks.amqp.agent.queue',
        'exchange'       =>    'maks.amqp.agent.exchange',
        'routing_key'    =>    'maks.amqp.agent.routing',
        'nowait'         =>    false,
        'arguments'      =>    [],
        'ticket'         =>    null
    ],
    'messageOptions' => [
        'body'          =>    '{}',
        'properties'    =>    [
            'content_type'        =>    'application/json',
            'content_encoding'    =>    'UTF-8',
            'delivery_mode'       =>    2
        ]
    ],
    'publishOptions' => [
        'msg'            =>    null,
        'exchange'       =>    'maks.amqp.agent.exchange',
        'routing_key'    =>    'maks.amqp.agent.routing',
        'mandatory'      =>    false,
        'immediate'      =>    false,
        'ticket'         =>    null
    ],

    // Consumer
    'qosOptions' => [
        'prefetch_size'     =>    null,
        'prefetch_count'    =>    5,
        'a_global'          =>    null
    ],
    'waitOptions' => [
        'allowed_methods'    =>    null,
        'non_blocking'       =>    true,
        'timeout'            =>    3600
    ],
    'consumeOptions' => [
        'queue'           =>    'maks.amqp.agent.queue',
        'consumer_tag'    =>    'maks.amqp.agent.consumer',
        'no_local'        =>    false,
        'no_ack'          =>    false,
        'exclusive'       =>    false,
        'nowait'          =>    false,
        'callback'        =>    'MAKS\AmqpAgent\Helper\Example::callback',
        'ticket'          =>    null,
        'arguments'       =>    []
    ],

    // Start of const class properties.
    // Only for reference, modifing them won't change anything
    'ackOptions' => [
        'multiple'    =>    false
    ],
    'nackOptions' => [
        'multiple'    =>    false,
        'requeue'     =>    true
    ],
    'getOptions' => [
        'queue'    =>    'maks.amqp.agent.queue',
        'no_ack'   =>    false,
        'ticket'   =>    null
    ],
    'cancelOptions' => [
        'consumer_tag'    =>    'maks.amqp.agent.consumer',
        'nowait'          =>    false,
        'noreturn'        =>    false
    ],
    'recoverOptions' => [
        'requeue'    =>    true,
    ],
    'rejectOptions' => [
        'requeue'    =>    true,
    ],
    // End of const class properties.
];
