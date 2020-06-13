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
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use MAKS\AmqpAgent\Worker\AbstractWorkerInterface;
use MAKS\AmqpAgent\Worker\WorkerCommandTrait;
use MAKS\AmqpAgent\Worker\WorkerMutationTrait;
use MAKS\AmqpAgent\Exception\AmqpAgentException;
use MAKS\AmqpAgent\Exception\MethodDoesNotExistException;
use MAKS\AmqpAgent\Exception\PropertyDoesNotExistException;

/**
 * An abstract class implementing the basic functionality of a worker.
 * @since 1.0.0
 * @api
 */
abstract class AbstractWorker implements AbstractWorkerInterface
{
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
     * AbstractWorker object constuctor.
     * @param array $connectionOptions [optional] The overrides for the default connection options of the worker.
     * @param array $channelOptions [optional] The overrides for the default channel options of the worker.
     * @param array $queueOptions [optional] The overrides for the default queue options of the worker.
     */
    public function __construct(array $connectionOptions = [], array $channelOptions = [], array $queueOptions = [])
    {
        $this->connectionOptions = [
            'host'                   =>    $connectionOptions['host'] ?? self::CONNECTION_OPTIONS['host'],
            'port'                   =>    $connectionOptions['port'] ?? self::CONNECTION_OPTIONS['port'],
            'user'                   =>    $connectionOptions['user'] ?? self::CONNECTION_OPTIONS['user'],
            'password'               =>    $connectionOptions['password'] ?? self::CONNECTION_OPTIONS['password'],
            'vhost'                  =>    $connectionOptions['vhost'] ?? self::CONNECTION_OPTIONS['vhost'],
            'insist'                 =>    $connectionOptions['insist'] ?? self::CONNECTION_OPTIONS['insist'],
            'login_method'           =>    $connectionOptions['login_method'] ?? self::CONNECTION_OPTIONS['login_method'],
            'login_response'         =>    $connectionOptions['login_response'] ?? self::CONNECTION_OPTIONS['login_response'],
            'locale'                 =>    $connectionOptions['locale'] ?? self::CONNECTION_OPTIONS['locale'],
            'connection_timeout'     =>    $connectionOptions['connection_timeout'] ?? self::CONNECTION_OPTIONS['connection_timeout'],
            'read_write_timeout'     =>    $connectionOptions['read_write_timeout'] ?? self::CONNECTION_OPTIONS['read_write_timeout'],
            'context'                =>    $connectionOptions['context'] ?? self::CONNECTION_OPTIONS['context'],
            'keepalive'              =>    $connectionOptions['keepalive'] ?? self::CONNECTION_OPTIONS['keepalive'],
            'heartbeat'              =>    $connectionOptions['heartbeat'] ?? self::CONNECTION_OPTIONS['heartbeat'],
            'channel_rpc_timeout'    =>    $connectionOptions['channel_rpc_timeout'] ?? self::CONNECTION_OPTIONS['channel_rpc_timeout'],
            'ssl_protocol'           =>    $connectionOptions['ssl_protocol'] ?? self::CONNECTION_OPTIONS['ssl_protocol']
        ];

        $this->channelOptions = [
            'channel_id'    =>    $channelOptions['channel_id'] ?? self::CHANNEL_OPTIONS['channel_id']
        ];

        $this->queueOptions = [
            'queue'          =>    $queueOptions['queue'] ?? self::QUEUE_OPTIONS['queue'],
            'passive'        =>    $queueOptions['passive'] ?? self::QUEUE_OPTIONS['passive'],
            'durable'        =>    $queueOptions['durable'] ?? self::QUEUE_OPTIONS['durable'],
            'exclusive'      =>    $queueOptions['exclusive'] ?? self::QUEUE_OPTIONS['exclusive'],
            'auto_delete'    =>    $queueOptions['auto_delete'] ?? self::QUEUE_OPTIONS['auto_delete'],
            'nowait'         =>    $queueOptions['nowait'] ?? self::QUEUE_OPTIONS['nowait'],
            'arguments'      =>    $queueOptions['arguments'] ?? self::QUEUE_OPTIONS['arguments'],
            'ticket'         =>    $queueOptions['ticket'] ?? self::QUEUE_OPTIONS['ticket']
        ];
    }

    /**
     * Closes the connection with RabbitMQ server before destoring the object.
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

        throw new PropertyDoesNotExistException(
            sprintf( // @codeCoverageIgnore
                // PHPUnit reports the line above as uncovered although the entire block is tested.
                'The requested property with the name "%s" does not exist!',
                $member
            )
        );
    }

    /**
     * Sets a class member via public property assignment notation.
     * @param string $member Property name.
     * @param array $array Array of overrides. The array type here is important, because only *Options properties should be overwritable.
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

        throw new PropertyDoesNotExistException(
            sprintf( // @codeCoverageIgnore
                // PHPUnit reports the line above as uncovered although the entire block is tested.
                'A property with the name "%s" is immutable or does not exist!',
                $member
            )
        );
    }

    /**
     * Throws an exception for calls to undefined methods.
     * @param string $function Function name.
     * @param array $arguments Function arguments.
     * @return mixed
     * @throws MethodDoesNotExistException
     */
    public function __call(string $function, array $arguments)
    {
        throw new MethodDoesNotExistException(
            sprintf( // @codeCoverageIgnore
                // PHPUnit reports the line above as uncovered although the entire block is tested.
                'The called method "%s" with the parameters "%s" does not exist!',
                $function,
                implode(', ', $arguments)
            )
        );
    }

    /**
     * Throws an exception for calls to undefined static methods.
     * @param string $function Function name.
     * @param array $arguments Function arguments.
     * @return mixed
     * @throws MethodDoesNotExistException
     */
    public static function __callStatic(string $function, array $arguments)
    {
        throw new MethodDoesNotExistException(
            sprintf( // @codeCoverageIgnore
                // PHPUnit reports the line above as uncovered although the entire block is tested.
                'The called static method "%s" with the parameters "%s" does not exist!',
                $function,
                implode(', ', $arguments)
            )
        );
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
                    $class->delivery_info['channel']->close();
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
            sprintf( // @codeCoverageIgnore
                // PHPUnit reports the line above as uncovered although the entire block is tested.
                'The passed parameter must be of type %s, %s or %s or a combination of them. Given parameter(s) has/have the type(s): %s!',
                AMQPStreamConnection::class,
                AMQPChannel::class,
                AMQPMessage::class,
                implode(', ', $parameters)
            )
        );
    }


    /**
     * Establishes a connection with RabbitMQ server and opens a channel for the worker in the opened connection.
     * @return self
     */
    public function connect(): self
    {
        if (empty($this->connection)) {
            $this->connection = new AMQPStreamConnection(
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
        }

        if (empty($this->channel)) {
            $this->channel = $this->connection->channel(
                $this->channelOptions['channel_id']
            );
        }

        return $this;
    }

    /**
     * Closes the connection with RabbitMQ server.
     * @return self
     */
    public function disconnect(): self
    {
        if (!empty($this->channel)) {
            $this->channel->close();
            $this->channel = null;
        }

        if (!empty($this->connection)) {
            $this->connection->close();
            $this->connection = null;
        }

        return $this;
    }

    /**
     * Executes self::disconnect() and self::connect() respectively.
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
            AmqpAgentException::rethrowException($error, __METHOD__ . '() failed!'); // @codeCoverageIgnore
        }

        if ($changes) {
            $this->mutateClassMember('queueOptions', $changes);
        }

        return $this;
    }

    /**
     * Returns an AMQPTable object.
     * @param array $array An array of the option wished to be turn into the an arguments object.
     * @return AMQPTable
     */
    public function arguments(array $array): AMQPTable
    {
        return new AMQPTable($array);
    }

    /**
     * Returns the default connection of the worker. If the worker is not connected, it returns null.
     * @return AMQPStreamConnection
     */
    public function getConnection(): ?AMQPStreamConnection
    {
        return $this->connection;
    }

    /**
     * Returns the default channel of the worker. If the worker is not connected, it returns null.
     * @return AMQPChannel
     */
    public function getChannel(): ?AMQPChannel
    {
        return $this->channel;
    }

    /**
     * Returns a new channel on the default connection of the worker. If the worker is not connected, it returns null.
     * @param array $parameters [optional] The overrides for the default channel options of the worker.
     * @return AMQPChannel|null
     */
    public function getNewChannel(array $parameters = null): ?AMQPChannel
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('channelOptions', $parameters);
        }

        $channel = null;
        if (isset($this->connection)) {
            $channel = $this->connection->channel(
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
     * @param int $channleId The id of the channel wished to be fetched.
     * @return AMQPChannel|null
     */
    public function getChannelById(int $channleId): ?AMQPChannel
    {
        $channels = $this->connection->channels;

        if (array_key_exists($channleId, $channels)) {
            return $channels[$channleId];
        }

        return null;
    }
}
