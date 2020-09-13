<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Worker;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use MAKS\AmqpAgent\Worker\AbstractWorkerInterface;

/**
 * An interface defining the basic methods of a consumer.
 * @since 1.0.0
 */
interface ConsumerInterface extends AbstractWorkerInterface
{
    /**
     * The default quality of service options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const QOS_OPTIONS = [
        'prefetch_size'     =>    null,
        'prefetch_count'    =>    5,
        'a_global'          =>    null
    ];

    /**
     * The default wait options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const WAIT_OPTIONS = [
        'allowed_methods'    =>    null,
        'non_blocking'       =>    true,
        'timeout'            =>    3600
    ];

    /**
     * The default consume options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const CONSUME_OPTIONS = [
        'queue'           =>    self::PREFIX . 'queue',
        'consumer_tag'    =>    self::PREFIX . 'consumer',
        'no_local'        =>    false,
        'no_ack'          =>    false,
        'exclusive'       =>    false,
        'nowait'          =>    false,
        'callback'        =>    'MAKS\AmqpAgent\Helper\Example::callback',
        'ticket'          =>    null,
        'arguments'       =>    []
    ];

    /**
     * The default acknowledge options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const ACK_OPTIONS = [
        'multiple'    =>    false
    ];

    /**
     * The default unacknowledge options that the worker should use when no overwrides are provided.
     * @var array
     */
    public const NACK_OPTIONS = [
        'multiple'    =>    false,
        'requeue'     =>    true
    ];

    /**
     * The default get options that the worker should use when no overrides are provided.
     * @var array
     */
    public const GET_OPTIONS = [
        'queue'    =>    self::PREFIX . 'queue',
        'no_ack'   =>    false,
        'ticket'   =>    null
    ];

    /**
     * The default cancel options that the worker should use when no overrides are provided.
     * @var array
     */
    public const CANCEL_OPTIONS = [
        'consumer_tag'    =>    self::PREFIX . 'consumer',
        'nowait'          =>    false,
        'noreturn'        =>    false
    ];

    /**
     * The default recover options that the worker should use when no overrides are provided.
     * @var array
     */
    public const RECOVER_OPTIONS = [
        'requeue'    =>    true,
    ];

    /**
     * The default reject options that the worker should use when no overrides are provided.
     * @var array
     */
    public const REJECT_OPTIONS = [
        'requeue'    =>    true,
    ];


    /**
     * Acknowledges an AMQP message object.
     * @param AMQPMessage $_message The message object that should be acknowledged.
     * @param array $parameters [optional] The overrides for the default acknowledge options.
     * @return void
     */
    public static function ack(AMQPMessage $_message, ?array $parameters = null): void;

    /**
     * Unacknowledges an AMQP message object.
     * @param AMQPChannel $_channel [optional] The channel that should be used. The method will try use the channel attached with the message if no channel was specified, although there is no guarantee this will work as this depends on the way the message was fetched.
     * @param AMQPMessage $_message The message object that should be unacknowledged.
     * @param array $parameters [optional] The overrides for the default exchange options.
     * @return void
     */
    public static function nack(?AMQPChannel $_channel = null, AMQPMessage $_message, ?array $parameters = null): void;

    /**
     * Gets a message object from a channel, direct access to a queue.
     * @deprecated 1.0.0 Direct queue access is not recommended. Use self::consume() instead.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array $parameters [optional] The overrides for the default get options.
     * @return AMQPMessage|null
     */
    public static function get(AMQPChannel $_channel, ?array $parameters = null): ?AMQPMessage;

    /**
     * Ends a queue consumer.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array $parameters [optional] The overrides for the default cancel options.
     * @return mixed
     */
    public static function cancel(AMQPChannel $_channel, ?array $parameters = null);

    /**
     * Redelivers unacknowledged messages
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array $parameters [optional] The overrides for the default recover options.
     * @return mixed
     */
    public static function recover(AMQPChannel $_channel, ?array $parameters = null);

    /**
     * Rejects an AMQP message object.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param AMQPMessage $_message The message object that should be rejected.
     * @param array $parameters [optional] The overrides for the default reject options.
     * @return void
     */
    public static function reject(AMQPChannel $_channel, AMQPMessage $_message, ?array $parameters = null): void;


    /**
     * Specifies the quility of service on the default channel of the worker's connection to RabbitMQ server.
     * @param array $parameters [optional] The overrides for the default quality of service options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function qos(?array $parameters = null, ?AMQPChannel $_channel = null);

    /**
     * Consumes messages from the default channel of the worker's connection to RabbitMQ server.
     * @param callback|array|string $callback [optional] The callback that the consumer uses to process the messages.
     * @param array $variables [optional] The variables that should be passed to the callback.
     * @param array $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function consume($callback = null, ?array $variables = null, ?array $parameters = null, ?AMQPChannel $_channel = null);

    /**
     * Checks wether the default channel is consuming.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return bool
     */
    public function isConsuming(?AMQPChannel $_channel = null): bool;

    /**
     * Keeps the connection to RabbitMQ server alive as long as the default channel is in used.
     * @param array $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function wait(?array $parameters = null, ?AMQPChannel $_channel = null);

    /**
     * Tries to keep the connection to RabbitMQ server alive as long as there are channels in used (default or not).
     * @param array $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPStreamConnection $_connection [optional] The connection that should be used instead of the default worker's connection.
     * @return self
     */
    public function waitForAll(?array $parameters = null, ?AMQPStreamConnection $_connection = null);
}
