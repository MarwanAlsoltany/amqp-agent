<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Config;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use MAKS\AmqpAgent\Config\AbstractParameters;

/**
 * A class that encapsulates all AMQP Agent parameters as constants.
 * @since 1.2.0
 */
final class AmqpAgentParameters extends AbstractParameters
{
    public const PREFIX = 'maks.amqp.agent.';

    public const COMMAND_PREFIX = '__COMMAND__';

    public const COMMAND_SYNTAX = [
        self::COMMAND_PREFIX => [
            'ACTION' => 'OBJECT',
            'PARAMS' => [
                'NAME'    => 'VALUE'
            ]
        ]
    ];

    public const CONNECTION_OPTIONS = [
        'host'                => 'localhost',
        'port'                => 5672,
        'user'                => 'guest',
        'password'            => 'guest',
        'vhost'               => '/',
        'insist'              => false,
        'login_method'        => 'AMQPLAIN',
        'login_response'      => null,
        'locale'              => 'en_US',
        'connection_timeout'  => 120,
        'read_write_timeout'  => 120,
        'context'             => null,
        'keepalive'           => true,
        'heartbeat'           => 60,
        'channel_rpc_timeout' => 120,
        'ssl_protocol'        => null
    ];

    public const CHANNEL_OPTIONS = [
        'channel_id' => null
    ];

    public const QUEUE_OPTIONS = [
        'queue'       => self::PREFIX . 'queue',
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => false,
        'nowait'      => false,
        'arguments'   => [],
        'ticket'      => null
    ];

    public const EXCHANGE_OPTIONS = [
        'exchange'    => self::PREFIX . 'exchange',
        'type'        => AMQPExchangeType::HEADERS,
        'passive'     => false,
        'durable'     => true,
        'auto_delete' => false,
        'internal'    => false,
        'nowait'      => false,
        'arguments'   => [],
        'ticket'      => null
    ];

    public const BIND_OPTIONS = [
        'queue'       => self::PREFIX . 'queue',
        'exchange'    => self::PREFIX . 'exchange',
        'routing_key' => self::PREFIX . 'routing',
        'nowait'      => false,
        'arguments'   => [],
        'ticket'      => null
    ];

    public const MESSAGE_OPTIONS = [
        'body'       => '{}',
        'properties' => [
            'content_type'     => 'application/json',
            'content_encoding' => 'UTF-8',
            'delivery_mode'    => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]
    ];

    public const PUBLISH_OPTIONS = [
        'msg'         => null,
        'exchange'    => self::PREFIX . 'exchange',
        'routing_key' => self::PREFIX . 'routing',
        'mandatory'   => false,
        'immediate'   => false,
        'ticket'      => null
    ];

    public const QOS_OPTIONS = [
        'prefetch_size'  => null,
        'prefetch_count' => 5,
        'a_global'       => null
    ];

    public const WAIT_OPTIONS = [
        'allowed_methods' => null,
        'non_blocking'    => true,
        'timeout'         => 3600
    ];

    public const CONSUME_OPTIONS = [
        'queue'        => self::PREFIX . 'queue',
        'consumer_tag' => self::PREFIX . 'consumer',
        'no_local'     => false,
        'no_ack'       => false,
        'exclusive'    => false,
        'nowait'       => false,
        'callback'     => 'MAKS\AmqpAgent\Helper\Example::callback',
        'ticket'       => null,
        'arguments'    => []
    ];

    public const ACK_OPTIONS = [
        'multiple' => false
    ];

    public const NACK_OPTIONS = [
        'multiple' => false,
        'requeue'  => true
    ];

    public const GET_OPTIONS = [
        'queue'  => self::PREFIX . 'queue',
        'no_ack' => false,
        'ticket' => null
    ];

    public const CANCEL_OPTIONS = [
        'consumer_tag'    =>    self::PREFIX . 'consumer',
        'nowait'          =>    false,
        'noreturn'        =>    false
    ];

    public const RECOVER_OPTIONS = [
        'requeue' => true,
    ];

    public const REJECT_OPTIONS = [
        'requeue' => true,
    ];
}
