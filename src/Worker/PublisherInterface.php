<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Worker;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use MAKS\AmqpAgent\Worker\AbstractWorkerInterface;

/**
 * An interface defining the basic methods of a publisher.
 * @since 1.0.0
 */
interface PublisherInterface extends AbstractWorkerInterface
{
    /**
     * The default exchange options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const EXCHANGE_OPTIONS = [
        'exchange'       =>    self::PREFIX . 'exchange',
        'type'           =>    AMQPExchangeType::HEADERS,
        'passive'        =>    false,
        'durable'        =>    true,
        'auto_delete'    =>    false,
        'internal'       =>    false,
        'nowait'         =>    false,
        'arguments'      =>    [],
        'ticket'         =>    null
    ];

    /**
     * The default bind options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const BIND_OPTIONS = [
        'queue'          =>    self::PREFIX . 'queue',
        'exchange'       =>    self::PREFIX . 'exchange',
        'routing_key'    =>    self::PREFIX . 'routing',
        'nowait'         =>    false,
        'arguments'      =>    [],
        'ticket'         =>    null
    ];

    /**
     * The default message options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const MESSAGE_OPTIONS = [
        'body'          =>    '{}',
        'properties'    =>    [
            'content_type'        =>    'application/json',
            'content_encoding'    =>    'UTF-8',
            'delivery_mode'       =>    AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]
    ];

    /**
     * The default publish options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const PUBLISH_OPTIONS = [
        'msg'            =>    null,
        'exchange'       =>    self::PREFIX . 'exchange',
        'routing_key'    =>    self::PREFIX . 'routing',
        'mandatory'      =>    false,
        'immediate'      =>    false,
        'ticket'         =>    null
    ];


    /**
     * Declares an exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param array $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function exchange(?array $parameters = null, ?AMQPChannel $_channel = null): self;

    /**
     * Bindes the default queue to the default exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param array $parameters [optional] The overrides for the default bind options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function bind(?array $parameters = null, ?AMQPChannel $_channel = null): self;

    /**
     * Returns an AMQPMessage object.
     * @param string $body The body of the message.
     * @param array $properties [optional] The overrides for the default properties of the default message options of the worker.
     * @return AMQPMessage
     */
    public function message(string $body, ?array $properties = null): AMQPMessage;

    /**
     * Publishes a message to the default exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param string|array|AMQPMessage $payload The body of the message or an array of body and properties for the message or a message object.
     * @param array $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function publish($payload, ?array $parameters = null, ?AMQPChannel $_channel = null): self;

    /**
     * Publishes a batch of messages to the default exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param AMQPMessage[] $messages An array of AMQPMessage objects.
     * @param int $batchSize [optional] The number of messages that should be published per batch.
     * @param string $_exchange [optional] The name of the exchange that should be used instead of the default worker's exchange name.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function publishBatch(array $messages, int $batchSize = 2500, ?string $_exchange = null, ?AMQPChannel $_channel = null): self;
}
