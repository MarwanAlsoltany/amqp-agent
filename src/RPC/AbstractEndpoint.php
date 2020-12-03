<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\RPC;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use MAKS\AmqpAgent\RPC\AbstractEndpointInterface;
use MAKS\AmqpAgent\Helper\EventTrait;
use MAKS\AmqpAgent\Exception\MagicMethodsExceptionsTrait;
use MAKS\AmqpAgent\Exception\RPCEndpointException;
use MAKS\AmqpAgent\Config\RPCEndpointParameters as Parameters;

/**
 * An abstract class implementing the basic functionality of an endpoint.
 * @since 2.0.0
 * @api
 */
abstract class AbstractEndpoint implements AbstractEndpointInterface
{
    use MagicMethodsExceptionsTrait;
    use EventTrait;

    /**
     * The connection options of the RPC endpoint.
     * @var array
     */
    protected $connectionOptions;

    /**
     * The queue name of the RPC endpoint.
     * @var string
     */
    protected $queueName;

    /**
     * Wether the endpoint is connected to RabbitMQ server or not.
     * @var bool
     */
    protected $connected;

    /**
     * The endpoint connection.
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * The endpoint channel.
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * The request body.
     * @var string
     */
    protected $requestBody;

    /**
     * Requests conveyor.
     * @var string
     */
    protected $requestQueue;

    /**
     * The response body.
     * @var string
     */
    protected $responseBody;

    /**
     * Responses conveyor.
     * @var string
     */
    protected $responseQueue;

    /**
     * Correlation ID of the last request/response.
     * @var string
     */
    protected $correlationId;


    /**
     * Class constructor.
     * @param array $connectionOptions [optional] The overrides for the default connection options of the RPC endpoint.
     * @param string $queueName [optional] The override for the default queue name of the RPC endpoint.
     */
    public function __construct(?array $connectionOptions = [], ?string $queueName = null)
    {
        $this->connectionOptions = Parameters::patch($connectionOptions, 'RPC_CONNECTION_OPTIONS');
        $this->queueName = empty($queueName) ? Parameters::RPC_QUEUE_NAME : $queueName;
    }

    /**
     * Closes the connection with RabbitMQ server before destroying the object.
     */
    public function __destruct()
    {
        $this->disconnect();
    }


    /**
     * Opens a connection with RabbitMQ server.
     * @param array|null $connectionOptions [optional] The overrides for the default connection options of the RPC endpoint.
     * @return self
     * @throws RPCEndpointException If the endpoint is already connected.
     */
    public function connect(?array $connectionOptions = [])
    {
        $this->connectionOptions = Parameters::patchWith(
            $connectionOptions ?? [],
            $this->connectionOptions
        );

        if ($this->isConnected()) {
            throw new RPCEndpointException('Endpoint is already connected!');
        }

        $parameters = array_values($this->connectionOptions);

        $this->connection = new AMQPStreamConnection(...$parameters);
        $this->trigger('connection.after.open', [$this->connection]);

        $this->channel = $this->connection->channel();
        $this->trigger('channel.after.open', [$this->channel]);

        return $this;
    }

    /**
     * Closes the connection with RabbitMQ server.
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->isConnected()) {
            $this->connected = null;

            $this->trigger('channel.before.close', [$this->channel]);
            $this->channel->close();

            $this->trigger('connection.before.close', [$this->connection]);
            $this->connection->close();
        }
    }

    /**
     * Returns wether the endpoint is connected or not.
     * @return bool
     */
    public function isConnected(): bool
    {
        $this->connected = (
            isset($this->connection) &&
            isset($this->channel) &&
            $this->connection->isConnected() &&
            $this->channel->is_open()
        );

        return $this->connected;
    }

    /**
     * Returns the connection used by the endpoint.
     * @return bool
     */
    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    /**
     * The time needed for the round-trip to RabbitMQ server in milliseconds.
     * Note that if the endpoint is not connected yet, this method will establish a new connection only for checking.
     * @return float A two decimal points rounded float.
     */
    final public function ping(): float
    {
        try {
            $pingConnection = $this->connection;
            if (!isset($pingConnection) || !$pingConnection->isConnected()) {
                $parameters = array_values($this->connectionOptions);
                $pingConnection = new AMQPStreamConnection(...$parameters);
            }
            $pingChannel = $pingConnection->channel();

            [$pingQueue] = $pingChannel->queue_declare(
                null,
                false,
                false,
                true,
                true
            );

            $pingChannel->basic_qos(
                null,
                1,
                null
            );

            $pingEcho = null;

            $pingChannel->basic_consume(
                $pingQueue,
                null,
                false,
                false,
                false,
                false,
                function ($message) use (&$pingEcho) {
                    $message->ack();
                    $pingEcho = $message->body;
                }
            );

            $pingStartTime = microtime(true);

            $pingChannel->basic_publish(
                new AMQPMessage(__FUNCTION__),
                null,
                $pingQueue
            );

            while (!$pingEcho) {
                $pingChannel->wait();
            }

            $pingEndTime = microtime(true);

            $pingChannel->queue_delete($pingQueue);

            if ($pingConnection === $this->connection) {
                $pingChannel->close();
            } else {
                $pingChannel->close();
                $pingConnection->close();
            }

            return round(($pingEndTime - $pingStartTime) * 1000, 2);
        } catch (Exception $error) {
            RPCEndpointException::rethrow($error);
        }
    }

    /**
     * Hooking method based on events to manipulate the request/response during the endpoint/message life cycle.
     * Check out `self::$events` via `self::getEvents()` after processing at least one request/response to see all available events.
     *
     * The parameters will be passed to the callback as follows:
     *      1. `$listenedOnObject` (first segment of event name e.g. `'connection.after.open'` will be `$connection`),
     *      2. `$calledOnObject` (the object this method was called on e.g. `$endpoint`),
     *      3. `$eventName` (the event was listened on e.g. `'connection.after.open'`).
     * ```
     * $endpoint->on('connection.after.open', function ($connection, $endpoint, $event) {
     *      ...
     * });
     * ```
     * @param string $event The event to listen on.
     * @param callable $callback The callback to execute.
     * @return self
     */
    final public function on(string $event, callable $callback)
    {
        $this->bind($event, function (...$arguments) use ($event, $callback) {
            call_user_func_array(
                $callback,
                array_merge(
                    $arguments,
                    [$this, $event]
                )
            );
        });

        return $this;
    }

    /**
     * Hook method to manipulate the message (request/response) when extending the class.
     * @return string
     */
    abstract protected function callback(AMQPMessage $message): string;
}
