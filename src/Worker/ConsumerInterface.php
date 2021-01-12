<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

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
     * Acknowledges an AMQP message object.
     * Starting from v1.1.1, you can use php-amqplib AMQPMessage::ack() method instead.
     * @param AMQPMessage $_message The message object that should be acknowledged.
     * @param array|null $parameters [optional] The overrides for the default acknowledge options.
     * @return void
     */
    public static function ack(AMQPMessage $_message, ?array $parameters = null): void;

    /**
     * Unacknowledges an AMQP message object.
     * Starting from v1.1.1, you can use php-amqplib AMQPMessage::nack() method instead.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used. The method will try using the channel attached with the message if no channel was specified, although there is no guarantee this will work as this depends on the way the message was fetched.
     * @param AMQPMessage $_message The message object that should be unacknowledged.
     * @param array|null $parameters [optional] The overrides for the default exchange options.
     * @return void
     */
    public static function nack(?AMQPChannel $_channel, AMQPMessage $_message, ?array $parameters = null): void;

    /**
     * Gets a message object from a channel, direct access to a queue.
     * @deprecated 1.0.0 Direct queue access is not recommended. Use `self::consume()` instead.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array|null $parameters [optional] The overrides for the default get options.
     * @return AMQPMessage|null
     */
    public static function get(AMQPChannel $_channel, ?array $parameters = null): ?AMQPMessage;

    /**
     * Ends a queue consumer.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array|null $parameters [optional] The overrides for the default cancel options.
     * @return mixed
     */
    public static function cancel(AMQPChannel $_channel, ?array $parameters = null);

    /**
     * Redelivers unacknowledged messages
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array|null $parameters [optional] The overrides for the default recover options.
     * @return mixed
     */
    public static function recover(AMQPChannel $_channel, ?array $parameters = null);

    /**
     * Rejects an AMQP message object.
     * @deprecated Starting from v1.1.1, you can use php-amqplib native AMQPMessage::reject() method instead.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param AMQPMessage $_message The message object that should be rejected.
     * @param array|null $parameters [optional] The overrides for the default reject options.
     * @return void
     */
    public static function reject(AMQPChannel $_channel, AMQPMessage $_message, ?array $parameters = null): void;


    /**
     * Specifies the quality of service on the default channel of the worker's connection to RabbitMQ server.
     * @param array|null $parameters [optional] The overrides for the default quality of service options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function qos(?array $parameters = null, ?AMQPChannel $_channel = null);

    /**
     * Consumes messages from the default channel of the worker's connection to RabbitMQ server.
     * @param callback|array|string|null $callback [optional] The callback that the consumer uses to process the messages.
     * @param array|null $variables [optional] The variables that should be passed to the callback.
     * @param array|null $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function consume($callback = null, ?array $variables = null, ?array $parameters = null, ?AMQPChannel $_channel = null);

    /**
     * Checks whether the default channel is consuming.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return bool
     */
    public function isConsuming(?AMQPChannel $_channel = null): bool;

    /**
     * Keeps the connection to RabbitMQ server alive as long as the default channel is in used.
     * @param array|null $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function wait(?array $parameters = null, ?AMQPChannel $_channel = null);

    /**
     * Tries to keep the connection to RabbitMQ server alive as long as there are channels in used (default or not).
     * @param array|null $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPStreamConnection|null $_connection [optional] The connection that should be used instead of the default worker's connection.
     * @return self
     */
    public function waitForAll(?array $parameters = null, ?AMQPStreamConnection $_connection = null);
}
