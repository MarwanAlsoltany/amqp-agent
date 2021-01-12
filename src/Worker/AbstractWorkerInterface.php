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
use PhpAmqpLib\Wire\AMQPTable;

/**
 * An interface defining the basic methods of a worker.
 * @since 1.0.0
 */
interface AbstractWorkerInterface
{
    /**
     * Closes the connection or the channel or both with RabbitMQ server.
     * @param AMQPStreamConnection|AMQPChannel|AMQPMessage ...$object The object that should be used to close the channel or the connection.
     * @return bool True on success.
     */
    public static function shutdown(...$object): bool;

    /**
     * Returns an AMQPTable object.
     * @param array $array An array of the option wished to be turn into the an arguments object.
     * @return AMQPTable
     */
    public static function arguments(array $array): AMQPTable;


    /**
     * Establishes a connection with RabbitMQ server and opens a channel for the worker in the opened connection, it also sets both of them as defaults.
     * @return self
     */
    public function connect();

    /**
     * Closes all open channels and connections with RabbitMQ server.
     * @return self
     */
    public function disconnect();

    /**
     * Executes `self::disconnect()` and `self::connect()` respectively. Note that this method will not restore old channels.
     * @return self
     */
    public function reconnect();

    /**
     * Declares a queue on the default channel of the worker's connection with RabbitMQ server.
     * @param array $parameters [optional] The overrides for the default queue options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function queue(?array $parameters = null, ?AMQPChannel $_channel = null);

    /**
     * Returns the default connection of the worker. If the worker is not connected, it returns null.
     * @since 1.1.0
     * @return AMQPStreamConnection|null
     */
    public function getConnection(): ?AMQPStreamConnection;

    /**
     * Sets the passed connection as the default connection of the worker.
     * @since 1.1.0
     * @param AMQPStreamConnection $connection The connection that should be as the default connection of the worker.
     * @return self
     */
    public function setConnection(AMQPStreamConnection $connection);

    /**
     * Opens a new connection to RabbitMQ server and returns it. Connections returned by this method pushed to connections array and are not set as default automatically.
     * @since 1.1.0
     * @param array|null $parameters
     * @return AMQPStreamConnection
     */
    public function getNewConnection(array $parameters = null): AMQPStreamConnection;

    /**
     * Returns the default channel of the worker. If the worker is not connected, it returns null.
     * @return AMQPChannel|null
     */
    public function getChannel(): ?AMQPChannel;

    /**
     * Sets the passed channel as the default channel of the worker.
     * @since 1.1.0
     * @param AMQPChannel $channel The channel that should be as the default channel of the worker.
     * @return self
     */
    public function setChannel(AMQPChannel $channel);

    /**
     * Returns a new channel on the the passed connection of the worker. If no connection is passed, it uses the default connection. If the worker is not connected, it returns null.
     * @param array|null $parameters [optional] The overrides for the default channel options of the worker.
     * @param AMQPStreamConnection|null $_connection [optional] The connection that should be used instead of the default worker's connection.
     * @return AMQPChannel|null
     */
    public function getNewChannel(array $parameters = null, ?AMQPStreamConnection $_connection = null): ?AMQPChannel;

    /**
     * Fetches a channel object identified by the passed id (channel_id). If not found, it returns null.
     * @param int $channelId The id of the channel wished to be fetched.
     * @param AMQPStreamConnection|null $_connection [optional] The connection that should be used instead of the default worker's connection.
     * @return AMQPChannel|null
     */
    public function getChannelById(int $channelId): ?AMQPChannel;
}
