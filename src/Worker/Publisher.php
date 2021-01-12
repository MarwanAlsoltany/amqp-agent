<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Worker;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exception\AMQPConnectionBlockedException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use MAKS\AmqpAgent\Worker\AbstractWorker;
use MAKS\AmqpAgent\Worker\PublisherInterface;
use MAKS\AmqpAgent\Worker\WorkerFacilitationInterface;
use MAKS\AmqpAgent\Exception\AmqpAgentException as Exception;
use MAKS\AmqpAgent\Config\PublisherParameters as Parameters;

/**
 * A class specialized in publishing. Implementing only the methods needed for a publisher.
 *
 * Example:
 * ```
 * $publisher = new Publisher();
 * $publisher->connect();
 * $publisher->queue();
 * $publisher->exchange();
 * $publisher->bind();
 * $publisher->publish('Some message!');
 * $publisher->disconnect();
 * ```
 *
 * @since 1.0.0
 * @api
 */
class Publisher extends AbstractWorker implements PublisherInterface, WorkerFacilitationInterface
{
    /**
     * The default exchange options that the worker should use when no overrides are provided.
     * @var array
     */
    protected $exchangeOptions;

    /**
     * The default bind options that the worker should use when no overrides are provided.
     * @var array
     */
    protected $bindOptions;

    /**
     * The default message options that the worker should use when no overrides are provided.
     * @var array
     */
    protected $messageOptions;

    /**
     * The default publish options that the worker should use when no overrides are provided.
     * @var array
     */
    protected $publishOptions;


    /**
     * Publisher object constructor.
     * @param array $connectionOptions [optional] The overrides for the default connection options of the worker.
     * @param array $channelOptions [optional] The overrides for the default channel options of the worker.
     * @param array $queueOptions [optional] The overrides for the default queue options of the worker.
     * @param array $exchangeOptions [optional] The overrides for the default exchange options of the worker.
     * @param array $bindOptions [optional] The overrides for the default bind options of the worker.
     * @param array $messageOptions [optional] The overrides for the default message options of the worker.
     * @param array $publishOptions [optional] The overrides for the default publish options of the worker.
     */
    public function __construct(
        array $connectionOptions = [],
        array $channelOptions = [],
        array $queueOptions = [],
        array $exchangeOptions = [],
        array $bindOptions = [],
        array $messageOptions = [],
        array $publishOptions = []
    ) {
        $this->exchangeOptions = Parameters::patch($exchangeOptions, 'EXCHANGE_OPTIONS');
        $this->bindOptions     = Parameters::patch($bindOptions, 'BIND_OPTIONS');
        $this->messageOptions  = Parameters::patch($messageOptions, 'MESSAGE_OPTIONS');
        $this->publishOptions  = Parameters::patch($publishOptions, 'PUBLISH_OPTIONS');

        parent::__construct($connectionOptions, $channelOptions, $queueOptions);
    }


    /**
     * Declares an exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param array|null $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws AMQPTimeoutException
     */
    public function exchange(?array $parameters = null, ?AMQPChannel $_channel = null): self
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('exchangeOptions', $parameters);
        }

        $channel = $_channel ?: $this->channel;

        try {
            $channel->exchange_declare(
                $this->exchangeOptions['exchange'],
                $this->exchangeOptions['type'],
                $this->exchangeOptions['passive'],
                $this->exchangeOptions['durable'],
                $this->exchangeOptions['auto_delete'],
                $this->exchangeOptions['internal'],
                $this->exchangeOptions['nowait'],
                $this->exchangeOptions['arguments'],
                $this->exchangeOptions['ticket']
            );
        } catch (AMQPTimeoutException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }

        if ($changes) {
            $this->mutateClassMember('exchangeOptions', $changes);
        }

        return $this;
    }

    /**
     * Binds the default queue to the default exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param array|null $parameters [optional] The overrides for the default bind options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws AMQPTimeoutException
     */
    public function bind(?array $parameters = null, ?AMQPChannel $_channel = null): self
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('bindOptions', $parameters);
        }

        $channel = $_channel ?: $this->channel;

        try {
            $channel->queue_bind(
                $this->bindOptions['queue'],
                $this->bindOptions['exchange'],
                $this->bindOptions['routing_key'],
                $this->bindOptions['nowait'],
                $this->bindOptions['arguments'],
                $this->bindOptions['ticket']
            );
        } catch (AMQPTimeoutException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }

        if ($changes) {
            $this->mutateClassMember('bindOptions', $changes);
        }

        return $this;
    }

    /**
     * Returns an AMQPMessage object.
     * @param string $body The body of the message.
     * @param array|null $properties [optional] The overrides for the default properties of the default message options of the worker.
     * @return AMQPMessage
     */
    public function message(string $body, ?array $properties = null): AMQPMessage
    {
        $changes = null;
        if ($properties) {
            $changes = $this->mutateClassSubMember('messageOptions', 'properties', $properties);
        }

        if ($body) {
            $this->messageOptions['body'] = $body;
        }

        $message = new AMQPMessage(
            $this->messageOptions['body'],
            $this->messageOptions['properties']
        );

        if ($changes) {
            $this->mutateClassSubMember('messageOptions', 'properties', $changes);
        }

        return $message;
    }

    /**
     * Publishes a message to the default exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param string|array|AMQPMessage $payload The body of the message or an array of body and properties for the message or a message object.
     * @param array|null $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws Exception|AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException
     */
    public function publish($payload, ?array $parameters = null, ?AMQPChannel $_channel = null): self
    {
        $changes = null;
        if ($parameters) {
            $changes = $this->mutateClassMember('publishOptions', $parameters);
        }

        $channel = $_channel ?: $this->channel;

        $originalMessage = $this->publishOptions['msg'];

        $message = $payload ?: $originalMessage;

        if ($message instanceof AMQPMessage) {
            $this->publishOptions['msg'] = $message;
        } elseif (is_array($message) && isset($message['body']) && isset($message['properties'])) {
            $this->publishOptions['msg'] = $this->message($message['body'], $message['properties']);
        } elseif (is_string($message)) {
            $this->publishOptions['msg'] = $this->message($message);
        } else {
            throw new Exception(
                sprintf(
                    'Payload must be a string, an array like %s, or an instance of "%s". The given parameter (data-type: %s) was none of them.',
                    '["body" => "Message body!", "properties" ["key" => "value"]]',
                    AMQPMessage::class,
                    is_object($payload) ? get_class($payload) : gettype($payload)
                )
            );
        }

        try {
            $channel->basic_publish(
                $this->publishOptions['msg'],
                $this->publishOptions['exchange'],
                $this->publishOptions['routing_key'],
                $this->publishOptions['mandatory'],
                $this->publishOptions['immediate'],
                $this->publishOptions['ticket']
            );
        } catch (AMQPChannelClosedException | AMQPConnectionClosedException | AMQPConnectionBlockedException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        } finally {
            // reverting messageOptions back to its state.
            $this->publishOptions['msg'] = $originalMessage;
        }

        if ($changes) {
            $this->mutateClassMember('publishOptions', $changes);
        }

        return $this;
    }

    /**
     * Publishes a batch of messages to the default exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param AMQPMessage[] $messages An array of AMQPMessage objects.
     * @param int $batchSize [optional] The number of messages that should be published per batch.
     * @param string|null $_exchange [optional] The name of the exchange that should be used instead of the default worker's exchange name.
     * @param AMQPChannel|null $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws Exception|AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException
     */
    public function publishBatch(array $messages, int $batchSize = 2500, ?string $_exchange = null, ?AMQPChannel $_channel = null): self
    {
        $channel = $_channel ?: $this->channel;
        $exchange = $_exchange ?: $this->publishOptions['exchange'];

        $count = count($messages);
        for ($i = 0; $i < $count; $i++) {
            if ($messages[$i] instanceof AMQPMessage) {
                $channel->batch_basic_publish($messages[$i], $exchange);
            } else {
                throw new Exception(
                    sprintf(
                        'Messages array elements must be of type "%s". Element in index "%d" was of type "%s".',
                        AMQPMessage::class,
                        $i,
                        is_object($messages[$i]) ? get_class($messages[$i]) : gettype($messages[$i])
                    )
                );
            }
            if ($i % $batchSize == 0) {
                try {
                    $channel->publish_batch();
                    // @codeCoverageIgnoreStart
                } catch (AMQPConnectionBlockedException $e) {
                    $tries = -1;
                    do {
                        sleep(1);
                        $tries++;
                    } while ($this->connection->isBlocked() && $tries >= 60);

                    $channel->publish_batch();
                } catch (AMQPChannelClosedException | AMQPConnectionClosedException | AMQPConnectionBlockedException $error) {
                    Exception::rethrow($error);
                    // @codeCoverageIgnoreEnd
                }
            }
        }

        try {
            $channel->publish_batch();
        } catch (AMQPChannelClosedException | AMQPConnectionClosedException | AMQPConnectionBlockedException $error) { // @codeCoverageIgnore
            Exception::rethrow($error); // @codeCoverageIgnore
        }

        return $this;
    }

    /**
     * Executes `self::connect()`, `self::queue()`, `self::exchange`, and `self::bind()` respectively.
     * @return self
     */
    public function prepare(): self
    {
        $this->connect();
        $this->queue();
        $this->exchange();
        $this->bind();

        return $this;
    }

    /**
     * Executes `self::connect()`, `self::queue()`, `self::exchange`, and `self::bind()`, `self::publish()`, and `self::disconnect()` respectively.
     * @param string[] $messages An array of strings.
     * @return bool
     * @throws Exception
     */
    public function work($messages): bool
    {
        $this->prepare();
        foreach ($messages as $message) {
            $this->publish($message);
        }
        $this->disconnect();

        return true;
    }
}
