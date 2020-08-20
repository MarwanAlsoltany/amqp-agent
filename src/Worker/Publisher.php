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
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exception\AMQPConnectionBlockedException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use MAKS\AmqpAgent\Worker\AbstractWorker;
use MAKS\AmqpAgent\Worker\PublisherInterface;
use MAKS\AmqpAgent\Worker\WorkerFacilitationInterface;
use MAKS\AmqpAgent\Exception\AmqpAgentException;

/**
 * A class specialized in publishing. Implementing only the methods needed for a publisher.
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
     * Publisher object constuctor.
     * @param array $connectionOptions [optional] The overrides for the default connection options of the worker.
     * @param array $channelOptions [optional] The overrides for the default channel options of the worker.
     * @param array $queueOptions [optional] The overrides for the default queue options of the worker.
     * @param array $exchangeOptions [optional] The overrides for the default exchange options of the worker.
     * @param array $bindOptions [optional] The overrides for the default bind options of the worker.
     * @param array $messageOptions [optional] The overrides for the default message options of the worker.
     * @param array $publishOptions [optional] The overrides for the default publish options of the worker.
     */
    public function __construct(array $connectionOptions = [], array $channelOptions = [], array $queueOptions = [], array $exchangeOptions = [], array $bindOptions = [], array $messageOptions = [], array $publishOptions = [])
    {
        $this->exchangeOptions = [
            'exchange'       =>    $exchangeOptions['exchange'] ?? self::EXCHANGE_OPTIONS['exchange'],
            'type'           =>    $exchangeOptions['type'] ?? self::EXCHANGE_OPTIONS['type'],
            'passive'        =>    $exchangeOptions['passive'] ?? self::EXCHANGE_OPTIONS['passive'],
            'durable'        =>    $exchangeOptions['durable'] ?? self::EXCHANGE_OPTIONS['durable'],
            'auto_delete'    =>    $exchangeOptions['auto_delete'] ?? self::EXCHANGE_OPTIONS['auto_delete'],
            'internal'       =>    $exchangeOptions['internal'] ?? self::EXCHANGE_OPTIONS['internal'],
            'nowait'         =>    $exchangeOptions['nowait'] ?? self::EXCHANGE_OPTIONS['nowait'],
            'arguments'      =>    $exchangeOptions['arguments'] ?? self::EXCHANGE_OPTIONS['arguments'],
            'ticket'         =>    $exchangeOptions['ticket'] ?? self::EXCHANGE_OPTIONS['ticket']
        ];

        $this->bindOptions = [
            'queue'          =>    $bindOptions['queue'] ?? self::BIND_OPTIONS['queue'],
            'exchange'       =>    $bindOptions['exchange'] ?? self::BIND_OPTIONS['exchange'],
            'routing_key'    =>    $bindOptions['routing_key'] ?? self::BIND_OPTIONS['routing_key'],
            'nowait'         =>    $bindOptions['nowait'] ?? self::BIND_OPTIONS['nowait'],
            'arguments'      =>    $bindOptions['arguments'] ?? self::BIND_OPTIONS['arguments'],
            'ticket'         =>    $bindOptions['ticket'] ?? self::BIND_OPTIONS['ticket']
        ];

        $this->messageOptions = [
            'body'           =>    $messageOptions['body'] ?? self::MESSAGE_OPTIONS['body'],
            'properties'     =>    $messageOptions['properties'] ?? self::MESSAGE_OPTIONS['properties']
        ];

        $this->publishOptions = [
            'msg'            =>    $publishOptions['msg'] ?? self::PUBLISH_OPTIONS['msg'],
            'exchange'       =>    $publishOptions['exchange'] ?? self::PUBLISH_OPTIONS['exchange'],
            'routing_key'    =>    $publishOptions['routing_key'] ?? self::PUBLISH_OPTIONS['routing_key'],
            'mandatory'      =>    $publishOptions['mandatory'] ?? self::PUBLISH_OPTIONS['mandatory'],
            'immediate'      =>    $publishOptions['immediate'] ?? self::PUBLISH_OPTIONS['immediate'],
            'ticket'         =>    $publishOptions['ticket'] ?? self::PUBLISH_OPTIONS['ticket']
        ];

        parent::__construct($connectionOptions, $channelOptions, $queueOptions);
    }


    /**
     * Declares an exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param array $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws AMQPTimeoutException
     */
    public function exchange(?array $parameters = null, ?AMQPChannel $_channel = null)
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
            AmqpAgentException::rethrowException($error, __METHOD__ . '() failed!'); // @codeCoverageIgnore
        }

        if ($changes) {
            $this->mutateClassMember('exchangeOptions', $changes);
        }

        return $this;
    }

    /**
     * Bindes the default queue to the default exchange on the default channel of the worker's connection to RabbitMQ server.
     * @param array $parameters [optional] The overrides for the default bind options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws AMQPTimeoutException
     */
    public function bind(?array $parameters = null, ?AMQPChannel $_channel = null)
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
            AmqpAgentException::rethrowException($error, __METHOD__ . '() failed!'); // @codeCoverageIgnore
        }

        if ($changes) {
            $this->mutateClassMember('bindOptions', $changes);
        }

        return $this;
    }

    /**
     * Returns an AMQPMessage object.
     * @param string $body The body of the message.
     * @param array $properties [optional] The overrides for the default properties of the default message options of the worker.
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
     * @param array $parameters [optional] The overrides for the default exchange options of the worker.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws AMQPInvalidArgumentException|AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException
     */
    public function publish($payload, ?array $parameters = null, ?AMQPChannel $_channel = null)
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
            throw new AMQPInvalidArgumentException(
                sprintf( // @codeCoverageIgnore
                    // PHPUnit reports the line above as uncovered although the entire block is tested.
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
        } catch (AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException $error) { // @codeCoverageIgnore
            AmqpAgentException::rethrowException($error, __METHOD__ . '() failed!'); // @codeCoverageIgnore
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
     * @param string $_exchange [optional] The name of the exchange that should be used instead of the default worker's exchange name.
     * @param AMQPChannel $_channel [optional] The channel that should be used instead of the default worker's channel.
     * @return self
     * @throws AMQPInvalidArgumentException|AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException
     */
    public function publishBatch(array $messages, int $batchSize = 2500, ?string $_exchange = null, ?AMQPChannel $_channel = null)
    {
        $channel = $_channel ?: $this->channel;
        $exchange = $_exchange ?: $this->publishOptions['exchange'];

        $count = count($messages);
        for ($i = 0; $i < $count; $i++) {
            if ($messages[$i] instanceof AMQPMessage) {
                $channel->batch_basic_publish($messages[$i], $exchange);
            } else {
                throw new AMQPInvalidArgumentException(
                    sprintf( // @codeCoverageIgnore
                        // PHPUnit reports the line above as uncovered although the entire block is tested.
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
                } catch (AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException $error) {
                    AmqpAgentException::rethrowException($error, __METHOD__ . '() failed!');
                    // @codeCoverageIgnoreEnd
                }
            }
        }

        try {
            $channel->publish_batch();
        } catch (AMQPChannelClosedException|AMQPConnectionClosedException|AMQPConnectionBlockedException $error) { // @codeCoverageIgnore
            AmqpAgentException::rethrowException($error, __METHOD__ . '() failed!'); // @codeCoverageIgnore
        }

        return $this;
    }

    /**
     * Executes self::connect(), self::queue(), self::exchange, and self::bind() respectively.
     * @return self
     */
    public function prepare()
    {
        $this->connect();
        $this->queue();
        $this->exchange();
        $this->bind();

        return $this;
    }

    /**
     * Executes self::connect(), self::queue(), self::exchange, self::bind(), self::publish(), and self::disconnect() respectively.
     * @param string[] $messages An array of strings.
     * @return bool
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
