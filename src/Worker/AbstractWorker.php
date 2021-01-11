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
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use MAKS\AmqpAgent\Worker\AbstractWorkerInterface;
use MAKS\AmqpAgent\Worker\WorkerCommandTrait;
use MAKS\AmqpAgent\Worker\WorkerMutationTrait;
use MAKS\AmqpAgent\Exception\MagicMethodsExceptionsTrait;
use MAKS\AmqpAgent\Exception\PropertyDoesNotExistException;
use MAKS\AmqpAgent\Exception\AmqpAgentException as Exception;
use MAKS\AmqpAgent\Config\AbstractWorkerParameters as Parameters;

/**
 * An abstract class implementing the basic functionality of a worker.
 * @since 1.0.0
 * @api
 */
abstract class AbstractWorker implements AbstractWorkerInterface
{
    use MagicMethodsExceptionsTrait {
        __get as private __get_MMET;
        __set as private __set_MMET;
    }
    use WorkerMutationTrait;
    use WorkerCommandTrait;

    /**
     * The default connection options that the worker should use when no overrides are provided.
     * @var array
     */
    protected $connectionOptions;

    /**
     * The default channel options that the worker should use when no overrides are provided.
     * @var array
     */
    protected $channelOptions;

    /**
     * The default queue options that the worker should use when no overrides are provided.
     * @var array
     */
    protected $queueOptions;

    /**
     * The default connection of the worker.
     * @var AMQPStreamConnection
     */
    public $connection;

    /**
     * The default channel of the worker.
     * @var AMQPChannel
     */
    public $channel;

    /**
     * All opened connections of the worker.
     * @var AMQPStreamConnection[]
     */
    public $connections = [];

    /**
     * All opened channels of the the worker.
     * @var AMQPChannel[]
     */
    public $channels = [];


    /**
     * AbstractWorker object constructor.
     * @param array $connectionOptions [optional] The overrides for the default connection options of the worker.
     * @param array $channelOptions [optional] The overrides for the default channel options of the worker.
     * @param array $queueOptions [optional] The overrides for the default queue options of the worker.
     */
    public function __construct(array $connectionOptions = [], array $channelOptions = [], array $queueOptions = [])
    {
        $this->connectionOptions = Parameters::patch($connectionOptions, 'CONNECTION_OPTIONS');
        $this->channelOptions    = Parameters::patch($channelOptions, 'CHANNEL_OPTIONS');
        $this->queueOptions      = Parameters::patch($queueOptions, 'QUEUE_OPTIONS');
    }

    /**
     * Closes the connection with RabbitMQ server before destroying the object.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Gets a class member via public property access notation.
     * @param string $member Property name.
     * @return mixed
     * @throws PropertyDoesNotExistException
     */
    public function __get(string $member)
    {
        $isMember = property_exists($this, $member);
        if ($isMember) {
            return $this->{$member};
        }

        $this->__get_MMET($member);
    }

    /**
     * Sets a class member via public property assignment notation.
     * @param string $member Property name.
     * @param array $array Array of overrides. The array type here is important, because only *Options properties should be overridable.
     * @return void
     * @throws PropertyDoesNotExistException
     */
    public function __set(string $member, array $array)
    {
        $isMember = property_exists($this, $member);
        $notProtected = $member !== 'mutation' ? true : false;

        if ($isMember && $notProtected) {
            $acceptedKeys = array_keys($this->{$member});
            foreach ($array as $key => $value) {
                if (in_array($key, $acceptedKeys)) {
                    $this->{$member}[$key] = $value;
                }
            }
            return;
        }

        $this->__set_MMET($member, $array);
    }


    /**
     * Closes the connection or the channel or both with RabbitMQ server.
     * @param AMQPStreamConnection|AMQPChannel|AMQPMessage ...$object The object that should be used to close the channel or the connection.
     * @return bool True on success.
     * @throws AMQPInvalidArgumentException
     */
    public static function shutdown(...$object): bool
    {
        $successful = true;
        $parameters = [];

        foreach ($object as $class) {
            $parameters[] = is_object($class) ? get_class($class) : gettype($class);
            if (
                $class instanceof AMQPStreamConnection ||
                $class instanceof AMQPChannel ||
                $class instanceof AMQPMessage
            ) {
                try {
                    if (!($class instanceof AMQPMessage)) {
                        $class->close();
                        continue;
                    }
                    $class->getChannel()->close();
                } catch (AMQPConnectionClosedException $e) {
                    // No need to throw the exception here as it's extraneous. This error
                    // happens when a channel gets closed multiple times in different ways.
                }
            } else {
                $successful = false;
            }
        }

        if ($successful) {
            return $successful;
        }

        throw new AMQPInvalidArgumentException(
            sprintf(
                'The passed parameter must be of type %s, %s or %s or a combination of them. Given parameter(s) has/have the type(s): %s!',
                AMQPStreamConnection::class,
                AMQPChannel::class,
                AMQPMessage::class,
                implode(', ', $parameters)
            )
        );
    }

    /**
     * Returns an AMQPTable object.
     * @param array $array An array of the option wished to be turn into the an arguments object.
     * @return AMQPTable
     */
    public static function arguments(array $array): AMQPTable
    {
        return new AMQPTable($array);
    }


    /**
     * Establishes a connection with RabbitMQ server and opens a channel for the worker in the opened connection, it also sets both of them as defaults.
     * @return self
     */
    public function connect(): self
    {
        if (empty($this->connection)) {
            $this->connection = $this->getNewConnection();
        }

        if (empty($this->channel)) {
            $this->channel = $this->getNewChannel();
        }

        return $this;
    }

    /**
     * Closes all open channels and connections with RabbitMQ server.
     * @return self
     */
    public function disconnect(): self
    {
        if (count($this->channels)) {
            foreach ($this->channels as $channel) {
                $channel->close();
            }
            $this->channel = null;
            $this->channels = [];
        }

        if (count($this->connections)) {
            foreach ($this->connections as $connection) {
                $connection->close();
            }
            $this->connection = null;
            $this->connections = [];
        }

        return $this;
    }

    /**
     * Executes `self::disconnect()` and `self::connect()` respectively. Note that this method will not restore old channels.
     * @return self
     */
    public function reconnect(): self
    {
        $this->disconnect();
        $this->connect();

        return $this;
    }

    /**
     * Declares a queue on the default channel of the worker's connection with RabbitMQ server.
     * @param array $parameters [optional] The overrides for the default queue options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws AMQPTimeoutException
     */
    public function queue(?array $parameters = null, ?AMQPChannel $_channel = null): self
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('queueOptions', $parameters);
        }

        $channel = $_channel ?: $this->channel;

        try {
            $channel->queue_declare(
                $this->queueOptions['queue'],
                $this->queueOptions['passive'],
                $this->queueOptions['durable'],
                $this->queueOptions['exclusive'],
                $this->queueOptions['auto_delete'],
                $this->queueOptions['nowait'],
                $this->queueOptions['arguments'],
                $this->queueOptions['ticket']
            );
        } catch (AMQPTimeoutException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }

        if ($changes) {
            $this->mutateClassMember('queueOptions', $changes);
        }

        return $this;
    }

    /**
     * Returns the default connection of the worker. If the worker is not connected, it returns null.
     * @since 1.1.0
     * @return AMQPStreamConnection|null
     */
    public function getConnection(): ?AMQPStreamConnection
    {
        return $this->connection;
    }

    /**
     * Sets the passed connection as the default connection of the worker.
     * @since 1.1.0
     * @param AMQPStreamConnection $connection The connection that should be as the default connection of the worker.
     * @return self
     */
    public function setConnection(AMQPStreamConnection $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Opens a new connection to RabbitMQ server and returns it. Connections returned by this method pushed to connections array and are not set as default automatically.
     * @since 1.1.0
     * @return AMQPStreamConnection
     */
    public function getNewConnection(array $parameters = null): AMQPStreamConnection
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('connectionOptions', $parameters);
        }

        $this->connections[] = $connection = new AMQPStreamConnection(
            $this->connectionOptions['host'],
            $this->connectionOptions['port'],
            $this->connectionOptions['user'],
            $this->connectionOptions['password'],
            $this->connectionOptions['vhost'],
            $this->connectionOptions['insist'],
            $this->connectionOptions['login_method'],
            $this->connectionOptions['login_response'],
            $this->connectionOptions['locale'],
            $this->connectionOptions['connection_timeout'],
            $this->connectionOptions['read_write_timeout'],
            $this->connectionOptions['context'],
            $this->connectionOptions['keepalive'],
            $this->connectionOptions['heartbeat'],
            $this->connectionOptions['channel_rpc_timeout'],
            $this->connectionOptions['ssl_protocol']
        );

        if ($changes) {
            $this->mutateClassMember('connectionOptions', $changes);
        }

        return $connection;
    }

    /**
     * Returns the default channel of the worker. If the worker is not connected, it returns null.
     * @return AMQPChannel|null
     */
    public function getChannel(): ?AMQPChannel
    {
        return $this->channel;
    }

    /**
     * Sets the passed channel as the default channel of the worker.
     * @since 1.1.0
     * @param AMQPChannel $channel The channel that should be as the default channel of the worker.
     * @return self
     */
    public function setChannel(AMQPChannel $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Returns a new channel on the the passed connection of the worker. If no connection is passed, it uses the default connection. If the worker is not connected, it returns null.
     * @param array $parameters [optional] The overrides for the default channel options of the worker.
     * @param AMQPStreamConnection $_connection [optional] The connection that should be used instead of the default worker's connection.
     * @return AMQPChannel|null
     */
    public function getNewChannel(array $parameters = null, ?AMQPStreamConnection $_connection = null): ?AMQPChannel
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('channelOptions', $parameters);
        }

        $connection = $_connection ?: $this->connection;

        $channel = null;
        if (isset($connection)) {
            $this->channels[] = $channel = $connection->channel(
                $this->channelOptions['channel_id']
            );
        }

        if ($changes) {
            $this->mutateClassMember('channelOptions', $changes);
        }

        return $channel;
    }

    /**
     * Fetches a channel object identified by the passed id (channel_id). If not found, it returns null.
     * @param int $channelId The id of the channel wished to be fetched.
     * @param AMQPStreamConnection $_connection [optional] The connection that should be used instead of the default worker's connection.
     * @return AMQPChannel|null
     */
    public function getChannelById(int $channelId, ?AMQPStreamConnection $_connection = null): ?AMQPChannel
    {
        $connection = $_connection ?: $this->connection;
        $channels = $connection->channels;

        if (array_key_exists($channelId, $channels)) {
            return $channels[$channelId];
        }

        return null;
    }
}
