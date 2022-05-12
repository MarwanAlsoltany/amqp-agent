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
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPOutOfBoundsException;
use MAKS\AmqpAgent\Worker\AbstractWorker;
use MAKS\AmqpAgent\Worker\ConsumerInterface;
use MAKS\AmqpAgent\Worker\WorkerFacilitationInterface;
use MAKS\AmqpAgent\Exception\CallbackDoesNotExistException;
use MAKS\AmqpAgent\Exception\AmqpAgentException as Exception;
use MAKS\AmqpAgent\Config\ConsumerParameters as Parameters;

/**
 * A class specialized in consuming. Implementing only the methods needed for a consumer.
 *
 * Example:
 * ```
 * $consumer = new Consumer();
 * $consumer->connect();
 * $consumer->queue();
 * $consumer->qos();
 * $consumer->consume('SomeNamespace\SomeClass::someCallback');
 * $consumer->wait();
 * $consumer->disconnect();
 * ```
 *
 * @since 1.0.0
 * @api
 */
class Consumer extends AbstractWorker implements ConsumerInterface, WorkerFacilitationInterface
{
    /**
     * The full quality of service options that should be used for the worker.
     * @var array
     */
    protected $qosOptions;

    /**
     * The full wait options that should be used for the worker.
     * @var array
     */
    protected $waitOptions;

    /**
     * The full consume options that should be used for the worker.
     * @var array
     */
    protected $consumeOptions;

    /**
     * The full acknowledgment options that should be used for the worker.
     * @var array
     */
    protected $ackOptions;

    /**
     * The full unacknowledgment options that should be used for the worker.
     * @var array
     */
    protected $nackOptions;


    /**
     * Consumer object constructor.
     * @param array $connectionOptions [optional] The overrides for the default connection options of the worker.
     * @param array $channelOptions [optional] The overrides for the default channel options of the worker.
     * @param array $queueOptions [optional] The overrides for the default queue options of the worker.
     * @param array $qosOptions [optional] The overrides for the default quality of service options of the worker.
     * @param array $waitOptions [optional] The overrides for the default wait options of the worker.
     * @param array $consumeOptions [optional] The overrides for the default consume options of the worker.
     */
    public function __construct(
        array $connectionOptions = [],
        array $channelOptions = [],
        array $queueOptions = [],
        array $qosOptions = [],
        array $waitOptions = [],
        array $consumeOptions = []
    ) {
        $this->qosOptions     = Parameters::patch($qosOptions, 'QOS_OPTIONS');
        $this->waitOptions    = Parameters::patch($waitOptions, 'WAIT_OPTIONS');
        $this->consumeOptions = Parameters::patch($consumeOptions, 'CONSUME_OPTIONS');
        $this->ackOptions     = Parameters::ACK_OPTIONS;
        $this->nackOptions    = Parameters::NACK_OPTIONS;

        parent::__construct($connectionOptions, $channelOptions, $queueOptions);
    }


    /**
     * Acknowledges an AMQP message object.
     * Starting from v1.1.1, you can use php-amqplib AMQPMessage::ack() method instead.
     * @param AMQPMessage $_message The message object that should be acknowledged.
     * @param array|null $parameters [optional] The overrides for the default acknowledge options.
     * @return void
     * @throws AMQPRuntimeException
     */
    public static function ack(AMQPMessage $_message, ?array $parameters = null): void
    {
        $parameters = Parameters::patch($parameters ?? [], 'ACK_OPTIONS');

        /**
         * If a consumer dies without sending an acknowledgement the AMQP broker will redeliver it
         * to another consumer. If none are available at the time, the broker will wait until at
         * least one consumer is registered for the same queue before attempting redelivery
         */
        try {
            /** @var AMQPChannel */
            $channel = $_message->getChannel();
            $channel->basic_ack(
                $_message->getDeliveryTag(),
                $parameters['multiple']
            );
        } catch (AMQPRuntimeException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }
    }

    /**
     * Unacknowledges an AMQP message object.
     * Starting from v1.1.1, you can use php-amqplib AMQPMessage::nack() method instead.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used. The method will try using the channel attached with the message if no channel was specified, although there is no guarantee this will work as this depends on the way the message was fetched.
     * @param AMQPMessage $_message The message object that should be unacknowledged.
     * @param array|null $parameters [optional] The overrides for the default exchange options.
     * @return void
     * @throws AMQPRuntimeException
     */
    public static function nack(?AMQPChannel $_channel, AMQPMessage $_message, ?array $parameters = null): void
    {
        $parameters = Parameters::patch($parameters ?? [], 'NACK_OPTIONS');

        try {
            /** @var AMQPChannel */
            $channel = $_channel ?? $_message->getChannel();
            $channel->basic_nack(
                $_message->getDeliveryTag(),
                $parameters['multiple'],
                $parameters['requeue']
            );
        } catch (AMQPRuntimeException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }
    }

    /**
     * Gets a message object from a channel, direct access to a queue.
     * @deprecated 1.0.0 Direct queue access is not recommended. Use `self::consume()` instead.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array|null $parameters [optional] The overrides for the default get options.
     * @return AMQPMessage|null
     * @throws AMQPTimeoutException
     */
    public static function get(AMQPChannel $_channel, ?array $parameters = null): ?AMQPMessage
    {
        $parameters = Parameters::patch($parameters ?? [], 'GET_OPTIONS');

        try {
            $return = $_channel->basic_get(
                $parameters['queue'],
                $parameters['no_ack'],
                $parameters['ticket']
            );
        } catch (AMQPTimeoutException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }

        return $return;
    }

    /**
     * Ends a queue consumer.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array|null $parameters [optional] The overrides for the default cancel options.
     * @return mixed
     * @throws AMQPTimeoutException
     */
    public static function cancel(AMQPChannel $_channel, ?array $parameters = null)
    {
        $parameters = Parameters::patch($parameters ?? [], 'CANCEL_OPTIONS');

        try {
            $return = $_channel->basic_cancel(
                $parameters['consumer_tag'],
                $parameters['nowait'],
                $parameters['noreturn']
            );
        } catch (AMQPTimeoutException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }

        return $return;
    }

    /**
     * Redelivers unacknowledged messages
     * @param AMQPChannel $_channel The channel that should be used.
     * @param array|null $parameters [optional] The overrides for the default recover options.
     * @return mixed
     * @throws AMQPTimeoutException
     */
    public static function recover(AMQPChannel $_channel, ?array $parameters = null)
    {
        $parameters = Parameters::patch($parameters ?? [], 'RECOVER_OPTIONS');

        try {
            $return = $_channel->basic_recover(
                $parameters['requeue']
            );
        } catch (AMQPTimeoutException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }

        return $return;
    }

    /**
     * Rejects an AMQP message object.
     * @deprecated Starting from v1.1.1, you can use php-amqplib native AMQPMessage::reject() method instead.
     * @param AMQPChannel $_channel The channel that should be used.
     * @param AMQPMessage $_message The message object that should be rejected.
     * @param array|null $parameters [optional] The overrides for the default reject options.
     * @return void
     * @throws AMQPRuntimeException
     */
    public static function reject(AMQPChannel $_channel, AMQPMessage $_message, ?array $parameters = null): void
    {
        $parameters = Parameters::patch($parameters ?? [], 'REJECT_OPTIONS');

        try {
            $_channel->basic_reject(
                $_message->getDeliveryTag(),
                $parameters['requeue']
            );
        } catch (AMQPRuntimeException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }
    }


    /**
     * Specifies the quality of service on the default channel of the worker's connection to RabbitMQ server.
     * @param array|null $parameters [optional] The overrides for the default quality of service options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     */
    public function qos(?array $parameters = null, ?AMQPChannel $_channel = null)
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('qosOptions', $parameters);
        }

        $channel = $_channel ?: $this->channel;

        $channel->basic_qos(
            $this->qosOptions['prefetch_size'],
            $this->qosOptions['prefetch_count'],
            $this->qosOptions['a_global']
        );

        if ($changes) {
            $this->mutateClassMember('qosOptions', $changes);
        }

        return $this;
    }

    /**
     * Consumes messages from the default channel of the worker's connection to RabbitMQ server.
     * @param callback|array|string|null $callback [optional] The callback that the consumer uses to process the messages.
     * @param array|null $variables [optional] The variables that should be passed to the callback.
     * @param array|null $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws CallbackDoesNotExistException|AMQPTimeoutException
     */
    public function consume($callback = null, ?array $variables = null, ?array $parameters = null, ?AMQPChannel $_channel = null)
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('consumeOptions', $parameters);
        }

        $channel = $_channel ?: $this->channel;

        $originalCallback = $this->consumeOptions['callback'];

        $callback = $callback ?: $originalCallback;

        if (is_callable($callback)) {
            if (is_array($callback) || is_string($callback)) {
                $this->consumeOptions['callback'] = function ($message) use ($callback, $variables) {
                    if ($variables) {
                        array_unshift($variables, $message);
                        call_user_func_array($callback, $variables);
                    } else {
                        call_user_func($callback, $message);
                    }
                };
            } else {
                $this->consumeOptions['callback'] = function ($message) use ($callback, $variables) {
                    // @codeCoverageIgnoreStart
                    if ($variables) {
                        $variables = array_values($variables);
                        $callback($message, ...$variables);
                    } else {
                        $callback($message);
                    }
                    // @codeCoverageIgnoreEnd
                };
            }
        } else {
            throw new CallbackDoesNotExistException(
                sprintf(
                    'The first parameter must be a valid callable, a callback, a variable containing a callback, a name of a function as string, a string like %s, or an array like %s. The given parameter (data-type: %s) was none of them.',
                    '"Foo\Bar\Baz::qux"',
                    '["Foo\Bar\Baz", "qux"]',
                    is_object($callback) ? get_class($callback) : gettype($callback)
                )
            );
        }

        try {
            $channel->basic_consume(
                $this->consumeOptions['queue'],
                $this->consumeOptions['consumer_tag'],
                $this->consumeOptions['no_local'],
                $this->consumeOptions['no_ack'],
                $this->consumeOptions['exclusive'],
                $this->consumeOptions['nowait'],
                $this->consumeOptions['callback'],
                $this->consumeOptions['ticket'],
                $this->consumeOptions['arguments']
            );
        } catch (AMQPTimeoutException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        } finally {
            // reverting consumeOptions back to its state.
            $this->consumeOptions['callback'] = $originalCallback;
        }


        if ($changes) {
            $this->mutateClassMember('consumeOptions', $changes);
        }

        register_shutdown_function([__CLASS__, 'shutdown'], ...array_values(array_merge($this->channels, $this->connections)));

        return $this;
    }

    /**
     * Checks whether the default channel is consuming.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return bool
     */
    public function isConsuming(?AMQPChannel $_channel = null): bool
    {
        $channel = $_channel ?: $this->channel;
        return $channel->is_consuming();
    }

    /**
     * Keeps the connection to RabbitMQ server alive as long as the default channel is in used.
     * @param array|null $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws AMQPOutOfBoundsException|AMQPRuntimeException|AMQPTimeoutException
     */
    public function wait(?array $parameters = null, ?AMQPChannel $_channel = null)
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('waitOptions', $parameters);
        }

        $channel = $_channel ?: $this->channel;

        ignore_user_abort(true);
        set_time_limit(0);

        while ($channel->is_consuming()) {
            try {
                $channel->wait(
                    $this->waitOptions['allowed_methods'],
                    $this->waitOptions['non_blocking'],
                    $this->waitOptions['timeout']
                );
            } catch (AMQPOutOfBoundsException | AMQPRuntimeException | AMQPTimeoutException $error) { // @codeCoverageIgnore
                Exception::rethrow($error); // @codeCoverageIgnore
            }
        }

        if ($changes) {
            $this->mutateClassMember('waitOptions', $changes);
        }

        return $this;
    }

    /**
     * Tries to keep the connection to RabbitMQ server alive as long as there are channels in used (default or not).
     * @param array|null $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPStreamConnection|null $_connection [optional] The connection that should be used instead of the default worker's connection.
     * @return self
     * @throws AMQPOutOfBoundsException|AMQPRuntimeException|AMQPTimeoutException
     */
    public function waitForAll(?array $parameters = null, ?AMQPStreamConnection $_connection = null)
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('waitOptions', $parameters);
        }

        $connection = $_connection ?: $this->connection;

        $active = false;
        $count = count($connection->channels);

        // $i starts with 1 because the first channel is the connection itself.
        // this means there are always at least two, one connection and one channel.
        for ($i = 1; $i < $count; $i++) {
            if (isset($connection->channels[$i])) {
                $active = true;
                break;
            }
        }

        ignore_user_abort(true);
        set_time_limit(0);

        while ($active) {
            try {
                $breaks = 0;
                for ($i = 1; $i < $count; $i++) {
                    if (isset($connection->channels[$i])) {
                        $channel = $connection->channels[$i];
                        if ($channel->is_consuming()) {
                            $channel->wait(
                                $this->waitOptions['allowed_methods'],
                                $this->waitOptions['non_blocking'],
                                $this->waitOptions['timeout']
                            );
                        }
                    } else {
                        $breaks++;
                    }
                    // refresh channels count
                    $count = count($connection->channels);
                }
                if ($breaks === $count - 1) {
                    // $active = false;
                    break;
                }
            } catch (AMQPOutOfBoundsException | AMQPRuntimeException | AMQPTimeoutException $error) { // @codeCoverageIgnore
                Exception::rethrow($error); // @codeCoverageIgnore
            }
        }

        if ($changes) {
            $this->mutateClassMember('waitOptions', $changes);
        }

        return $this;
    }

    /**
     * Executes `self::connect()`, `self::queue()`, and `self::qos()` respectively (note that `self::wait()` needs to be executed after `self::consume()`).
     * @return self
     */
    public function prepare()
    {
        $this->connect();
        $this->queue();
        $this->qos();

        return $this;
    }

    /**
     * Executes `self::connect()`, `self::queue()`, `self::qos()`, `self::consume()`, `self::wait()`, and `self::disconnect()` respectively.
     * @param callback|array|string $callback The callback that the consumer should use to process the messages (same as `self::consume()`).
     * @return void
     * @throws Exception
     */
    public function work($callback): void
    {
        try {
            $this->prepare();
            $this->consume($callback);
            $this->wait();
            $this->disconnect();
        } catch (Exception $error) {
            Exception::rethrow($error, null, false);
        }
    }
}
