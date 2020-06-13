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
use PhpAmqpLib\Wire\AMQPTable;

/**
 * An interface defining the basic methods of a worker.
 * @since 1.0.0
 */
interface AbstractWorkerInterface
{
    /**
     * The default prefix for naming that is used when no name is provided.
     * @var array
     */
    public const PREFIX = 'maks.amqp.agent.';

    /**
     * The default connection options that the worker should use when no overrides are provided.
     * @var array
     */
    public const CONNECTION_OPTIONS = [
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
    ];

    /**
     * The default channel options that the worker should use when no overrides are provided.
     * @var array
     */
    public const CHANNEL_OPTIONS = [
        'channel_id'    =>    null
    ];

    /**
     * The default queue options that the worker should use when no overrides are provided.
     * @var array
     */
    public const QUEUE_OPTIONS = [
        'queue'          =>    self::PREFIX . 'queue',
        'passive'        =>    false,
        'durable'        =>    true,
        'exclusive'      =>    false,
        'auto_delete'    =>    false,
        'nowait'         =>    false,
        'arguments'      =>    [],
        'ticket'         =>    null
    ];


    /**
     * Closes the connection or the channel or both with RabbitMQ server.
     * @param AMQPStreamConnection|AMQPChannel|AMQPMessage ...$object The object that should be used to close the channel or the connection.
     * @return bool True on success.
     */
    public static function shutdown(...$object): bool;


    /**
     * Establishes a connection with RabbitMQ server and opens a channel for the worker in the opened connection.
     * @return self
     */
    public function connect(): self;

    /**
     * Closes the connection with RabbitMQ server.
     * @return self
     */
    public function disconnect(): self;

    /**
     * Executes self::disconnect() and self::connect() respectively.
     * @return self
     */
    public function reconnect(): self;

    /**
     * Declares a queue on the default channel of the worker's connection with RabbitMQ server.
     * @param array $parameters [optional] The overrides for the default queue options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function queue(?array $parameters = null, ?AMQPChannel $_channel = null): self;

    /**
     * Returns an AMQPTable object.
     * @param array $array An array of the option wished to be turn into the an arguments object.
     * @return AMQPTable
     */
    public function arguments(array $array): AMQPTable;

    /**
     * Returns the default connection of the worker. If the worker is not connected, it returns null.
     * @return AMQPStreamConnection
     */
    public function getConnection(): ?AMQPStreamConnection;

    /**
     * Returns the default channel of the worker. If the worker is not connected, it returns null.
     * @return AMQPChannel
     */
    public function getChannel(): ?AMQPChannel;

    /**
     * Returns a new channel on the default connection of the worker. If the worker is not connected, it returns null.
     * @param array $parameters [optional] The overrides for the default channel options of the worker.
     * @return AMQPChannel|null
     */
    public function getNewChannel(array $parameters = null): ?AMQPChannel;

    /**
     * Fetches a channel object identified by the passed id (channel_id). If not found, it returns null.
     * @param int $channleId The id of the channel wished to be fetched.
     * @return AMQPChannel|null
     */
    public function getChannelById(int $channleId): ?AMQPChannel;
}
