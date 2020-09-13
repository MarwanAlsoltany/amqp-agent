<?php

namespace MAKS\AmqpAgent\Test\Worker;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Worker\Consumer;
use MAKS\AmqpAgent\Helper\Serializer;
use MAKS\AmqpAgent\Helper\Example;
use MAKS\AmqpAgent\Exception\CallbackDoesNotExistException;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumerTest extends TestCase
{
    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $messages;

    public function setUp(): void
    {
        parent::setUp();
        $this->consumer = new Consumer();
        $this->serializer = new Serializer();
        $this->messages = [];
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->consumer);
        unset($this->serializer);
        unset($this->messages);
    }

    public function testQosReturnsSelfAndOverrideParametersTakeEffect()
    {
        $consumer = $this->consumer->connect()->queue()->qos();
        $this->assertEquals($this->consumer, $consumer);
    }

    public function testIsConsumingReturnsFalseWhenCalledOnAnIdleChannel()
    {
        $this->consumer->connect();
        $false = $this->consumer->isConsuming();
        $this->assertFalse($false);
    }

    public function testConsumeConsumesMessagesFromRabbitMQServerAndAknowledgesThem()
    {
        $this->consumer->connect();
        $this->consumer->queue(
            [
                'queue' => 'maks.amqp.agent.queue.test'
            ]
        );
        $this->consumer->qos(
            [
                'prefetch_count' => 3
            ]
        );
        $this->consumer->consume(
            [
                __CLASS__,
                'consumerTestCallback'
            ],
            [
                &$this->messages,
                &$this->serializer,
                &$this->consumer
            ],
            [
                'queue' => 'maks.amqp.agent.queue.test'
            ]
        );
        $this->consumer->wait(
            [
                'timeout' => 7200
            ]
        );

        $this->assertTrue(sizeof($this->messages) > 0 ? true : false);
    }

    public function testConsumeRaisesAnExceptionIfInvalidCallbackIsPassed()
    {
        $this->expectException(CallbackDoesNotExistException::class);
        $error = $this->consumer->prepare()->consume(
            [
                __CLASS__,
                'notConsumerTestCallback'
            ]
        );
    }

    public function testWaitForAllWaitsForMultipleChannelAndReturnsSelf()
    {
        // this will use maks.amqp.agent.queue, 4 start commands, 5 close commands
        $this->consumer->prepare();
        $this->consumer->consume(
            __CLASS__.'::consumerTestCallback',
            [
                &$this->messages,
                &$this->serializer,
                &$this->consumer
            ]
        );
        $consumer = $this->consumer->waitForAll(
            [
                'non_blocking' => true
            ]
        );

        $this->assertInstanceOf(Consumer::class, $consumer);
    }

    public function testConsumingMessagesFromRabbitMQServerViaWorkMethod()
    {
        // since Consumer::work() takes only a callback, there is no way to check
        // if any messages were consumed. A workaround is publishing only a command message
        // to close the channel and using Consumer::shutdown() as a callback. This will
        // result in Consumer::work() finishing executing and returning true.

        // this will use maks.amqp.agent.queue, 1 or 0 close command
        $true = $this->consumer->work([Consumer::class, 'shutdown']);

        $this->assertTrue($true);
    }

    public function testGetGetsAMessageFromRabbitMQServer()
    {
        $channel = $this->consumer->prepare()->getChannel();

        $message = Consumer::get(
            $channel,
            [
                'no_ack' => true,
                'queue' => 'maks.amqp.agent.queue.message.types.test'
            ]
        );

        $this->assertTrue((strlen($message->body) > 0));
    }

    public function testGetGetsMessagesFromRabbitMQServerAndUnaknowledgesThem()
    {
        $channel = $this->consumer->connect()->getChannel();

        $messageOne = Consumer::get(
            $channel,
            [
                'no_ack' => false,
                'queue' => 'maks.amqp.agent.queue.message.types.test'
            ]
        );
        Consumer::nack(
            $channel,
            $messageOne,
            [
                'multiple' => false,
                'requeue' => true
            ]
        );

        $messageTwo = Consumer::get(
            $channel,
            [
                'no_ack' => false,
                'queue' => 'maks.amqp.agent.queue.message.types.test'
            ]
        );
        Consumer::nack(
            $channel,
            $messageTwo,
            [
                'multiple' => false,
                'requeue' => true
            ]
        );

        $this->assertEquals($messageTwo->body, $messageOne->body);
    }

    public function testCancelCancelsAConsumerAndRedeiliversMessagesToRabbitMQServer()
    {
        $this->consumer->prepare();
        $this->consumer->consume(
            __CLASS__.'::consumerTestCallback',
            [
                &$this->messages,
                &$this->serializer
            ],
            [
                'queue' => 'maks.amqp.agent.queue.message.types.test',
                'consumer_tag' => 'maks.amqp.agent.consumer.message.types.test',
            ]
        );

        $result = Consumer::cancel(
            $this->consumer->getChannel(),
            [
                'consumer_tag' => 'maks.amqp.agent.consumer.message.types.test',
                'noreturn' => true
            ]
        );

        // CONFIRMED: messages where redelivered back to RabbitMQ server.

        // Because Consumer::cancel() returns mixed there
        // isn't any way to assert against a specific type
        $this->assertNotEmpty($result);
    }

    public function testRecoverRedeliversAllUnaknowledgeMessagesToRabbitMQServer()
    {
        $this->consumer->connect();
        $this->consumer->queue();
        $this->consumer->qos(
            [
                'prefetch_count' => 50
            ]
        );

        $func = function ($message) {
            return usleep(10000);
        };
        $args = [
            'uselessStuff' => null
        ];

        $this->consumer->consume(
            $func,
            $args
        );

        usleep(10000);

        $result = Consumer::recover(
            $this->consumer->getChannel(),
            [
                'requeue' => true
            ]
        );

        $this->consumer->disconnect();

        // CONFIRMED: 50 messages where fetched and then delivered back to RabbitMQ server.

        // Because Consumer::recover() returns mixed there
        // isn't anyway to assert against a specific type
        $this->assertEmpty($result);
    }

    public function testRejectRejectsAMessageAndRedeiliversMessagesToRabbitMQServer()
    {
        $this->consumer->connect();
        $channel = $this->consumer->getChannel();

        $messageOne = Consumer::get(
            $channel,
            [
                'no_ack' => false,
                'queue' => 'maks.amqp.agent.queue.message.types.test'
            ]
        );
        Consumer::reject(
            $channel,
            $messageOne,
            [
                'requeue' => true
            ]
        );

        $messageTwo = Consumer::get(
            $channel,
            [
                'no_ack' => true,
                'queue' => 'maks.amqp.agent.queue.message.types.test'
            ]
        );

        // if regect worked the second get must get the same message.
        $this->assertEquals($messageTwo->getMessageCount(), $messageOne->getMessageCount());
    }

    public function testConsumingMessagesFromRabbitMQServerUsingConfigCallback()
    {
        Example::$logToFile = false;

        $this->consumer->connect();

        // consume left messages in maks.amqp.agent.queue
        $this->consumer->qos(
            [
                'prefetch_count' => 1
            ]
        );
        $this->consumer->consume(
            null,
            null,
            [
                'queue' => 'maks.amqp.agent.queue'
            ]
        );
        $consumer1 = $this->consumer->wait();

        // consume left messages in maks.amqp.agent.queue.message.types.test
        $mttChannel = $this->consumer->getNewChannel();
        $this->consumer->qos(
            [
                'prefetch_count' => 1
            ],
            $mttChannel
        );
        $this->consumer->consume(
            null,
            null,
            [
                'queue' => 'maks.amqp.agent.queue.message.types.test'
            ],
            $mttChannel
        );
        $consumer2 = $this->consumer->wait(null, $mttChannel);

        $this->assertEquals($consumer1, $consumer2);
        $this->assertEquals($this->consumer, $consumer1);
    }

    /**
     * A callback used to test the consumer.
     * @param AMQPMessage $message
     * @param array $messages
     * @param Serializer $serializer
     * @return void
     */
    public static function consumerTestCallback(AMQPMessage $message, array &$messages, Serializer &$serializer, Consumer &$consumer = null)
    {
        usleep(10000);

        $data = $serializer->unserialize($message->body, 'JSON');

        if (Consumer::isCommand($data)) {
            Consumer::ack($message);
            usleep(25000); // For acknowledgment to take effect.
            if (Consumer::hasCommand($data, 'close', 'channel')) {
                Consumer::shutdown($message);
            } elseif (Consumer::getCommand($data, 'start') === 'consumer') {
                $channel = $consumer->getNewChannel();
                $consumer->queue(
                    [
                        'queue' => 'maks.amqp.agent.queue'
                    ],
                    $channel
                );
                $consumer->qos(
                    [
                        'prefetch_count' => 1
                    ],
                    $channel
                );
                $consumer->consume(
                    'MAKS\AmqpAgent\Test\Worker\ConsumerTest::consumerTestCallback',
                    [
                        &$messages,
                        &$serializer,
                        &$consumer
                    ],
                    [
                        'consumer_tag' => 'maks.amqp.agent.consumer-callback-' . uniqid()
                    ],
                    $channel
                );
            }
            return;
        }

        $messages[] = $message->body;
        Consumer::ack($message);
    }
}
