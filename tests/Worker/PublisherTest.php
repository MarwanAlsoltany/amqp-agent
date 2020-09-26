<?php

namespace MAKS\AmqpAgent\Tests\Worker;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Worker\Publisher;
use MAKS\AmqpAgent\Helper\Serializer;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Message\AMQPMessage;

class PublisherTest extends TestCase
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var Serializer
     */
    private $serializer;


    public function setUp(): void
    {
        parent::setUp();
        $this->publisher = new Publisher();
        $this->serializer = new Serializer();
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->publisher);
        unset($this->serializer);
    }

    public function testQueueReturnsSelfAndOverrideParametersTakeEffect()
    {
        $publisher = $this->publisher->connect()->queue([
            'queue' => 'maks.amqp.agent.queue'
        ]);
        $this->assertEquals($publisher, $this->publisher);
    }

    public function testExchangeReturnsSelfAndOverrideParametersTakeEffect()
    {
        $publisher = $this->publisher->connect()->queue()->exchange([
            'exchange' => 'maks.amqp.agent.exchange'
        ]);
        $this->assertEquals($publisher, $this->publisher);
    }

    public function testBindReturnsSelfAndOverrideParametersTakeEffect()
    {
        $publisher = $this->publisher->connect()->queue()->exchange()->bind([
            'queue' => 'maks.amqp.agent.queue',
            'exchange' => 'maks.amqp.agent.exchange',
        ]);
        $this->assertEquals($publisher, $this->publisher);
    }

    public function testMessageReturnsAnAMQPMessageAndThePassedParametersWorkAsExpected()
    {
        $body = 'Test message!';
        $timestamp = time();
        $message = $this->publisher->message($body, ['timestamp' => $timestamp]);
        $this->assertInstanceOf(AMQPMessage::class, $message);
        $this->assertEquals($body, $message->getBody());
        $this->assertEquals($timestamp, $message->get('timestamp'));
    }

    public function testPublishRaisesAnExceptionIfUnexpectedParameterIsPassed()
    {
        $this->expectException(AMQPInvalidArgumentException::class);

        $this->publisher->connect();
        $this->publisher->queue([
            'queue' => 'this.is.not.a.normal.queue.name'
        ]);
        $this->publisher->exchange([
            'exchange' => 'this.is.not.a.normal.exchange.name'
        ]);
        $this->publisher->bind([
            'queue' => 'this.is.not.a.normal.queue.name',
            'exchange' => 'this.is.not.a.normal.exchange.name'
        ]);
        $this->publisher->publish(null, [
            'exchange' => 'this.is.not.a.normal.exchange.name',
        ]);
    }

    public function testPublishBatchAndPublishPublishesMessagesToRabbitMQServerAndOverrideParametersTakeEffect()
    {
        $this->publisher->connect([
            'user' => 'guest',
            'password' => 'guest'
        ])->queue([
            'queue' => 'maks.amqp.agent.queue.test'
        ])->exchange([
            'exchange' => 'maks.amqp.agent.exchange.test'
        ])->bind([
            'queue' => 'maks.amqp.agent.queue.test',
            'exchange' => 'maks.amqp.agent.exchange.test'
        ]);

        $messages = [];
        for ($i = 1; $i <= 1000; $i++) {
            $timestamp = time();
            $date = date('F j, Y, H:m:s', $timestamp);
            $messages[] = $this->publisher->message(
                $this->serializer->serialize(
                    ["MSG-{$timestamp}-{$i}" => "Test message number {$i}. This message was published on {$date}."],
                    'JSON'
                ),
                [
                    'timestamp' => $timestamp
                ]
            );
        }

        $publisher = $this->publisher->publishBatch(
            $messages,
            250,
            'maks.amqp.agent.exchange.test'
        );

        // Publishing channel closing command and making
        // it expire after one hour it it was not consumed.
        $this->publisher->publish(
            [
                'body' => $this->serializer->serialize(
                    Publisher::makeCommand('close', 'channel'),
                    'JSON'
                ),
                'properties' => [
                    'expiration' => 3.6e+6
                ]
            ],
            [
                'exchange' => 'maks.amqp.agent.exchange.test'
            ]
        );

        // CONFIRMED: 5000 + 1 messages where published to the queue successfuly.
        // Note that ConsumerTest will fail if the messages were not published.
        $this->assertEquals($this->publisher, $publisher);
    }

    public function testPublishBatchRaisesAnExceptionIfUnexpectedParameterIsPassed()
    {
        $this->publisher->prepare();

        $messages = [
            $this->publisher->message("This's test message number 1!"),
            null,
            $this->publisher->message("This's test message number 2!")
        ];

        $this->expectException(AMQPInvalidArgumentException::class);
        $error = $this->publisher->publishBatch($messages, 1);
    }

    public function testPublishPublishesMessagesToRabbitMQServerWithDifferentTypesOfMessages()
    {
        $this->publisher->connect();
        $this->publisher->queue([
            'queue' => 'maks.amqp.agent.queue.message.types.test'
        ]);
        $this->publisher->exchange([
            'exchange' => 'maks.amqp.agent.exchange.message.types.test'
        ]);
        $this->publisher->bind([
            'queue' => 'maks.amqp.agent.queue.message.types.test',
            'exchange' => 'maks.amqp.agent.exchange.message.types.test'
        ]);
        $this->publisher->publish(
            "This's a good way of publishing messages, it's also the simplest!",
            [
                'exchange' => 'maks.amqp.agent.exchange.message.types.test'
            ]
        );
        $this->publisher->publish(
            [
                'body' => 'Wrap the message in an array to supply additional AMQPMessage properties.',
                'properties' => [
                    'timestamp' => time()
                ]
            ],
            [
                'exchange' => 'maks.amqp.agent.exchange.message.types.test'
            ]
        );
        $this->publisher->publish(
            '{"passing-serialized-data": "is the best way of publishing messages!", "isRecommended": true}',
            [
                'exchange' => 'maks.amqp.agent.exchange.message.types.test'
            ]
        );
        $this->publisher->publish(
            $this->publisher->message("This's also good way of publishing messages!"),
            [
                'exchange' => 'maks.amqp.agent.exchange.message.types.test'
            ]
        );
        $publisher = $this->publisher->publish(
            [
                'body' => $this->serializer->serialize(
                    Publisher::makeCommand('close', 'channel'),
                    'JSON'
                ),
                'properties' => [
                    'expiration' => 3.6e+6
                ]
            ],
            [
                'exchange' => 'maks.amqp.agent.exchange.message.types.test'
            ]
        );

        // CONFIRMED: these 4 + 1 messages where published to the queue successfuly.
        // Note that ConsumerTest will fail if the messages were not published
        $this->assertEquals($this->publisher, $publisher);
    }

    public function testPublishingMessagesToRabbitMQServerViaWorkMethod()
    {
        $messages = [];

        for ($i = 1; $i <= 4000; $i++) {
            $timestamp = time();
            $date = date('F j, Y, H:m:s', $timestamp);
            $messages[] = $this->serializer->serialize(
                ["MSG-{$timestamp}-{$i}" => "Test message number {$i}. This message was published on {$date}."],
                'JSON'
            );
            if ($i % 1000 == 0 && $i != 4000) {
                $messages[] = $this->serializer->serialize(
                    Publisher::makeCommand('start', 'consumer'),
                    'JSON'
                );
            }
        }

        // This queue will be consumed by multiple consumers.
        // That's why it has 5 channel closing command.
        $i = 5;
        while ($i >= 1) {
            $messages[] = [
                'body' => $this->serializer->serialize(
                    Publisher::makeCommand('close', 'channel'),
                    'JSON'
                ),
                'properties' => [
                    'expiration' => 3.6e+6
                ]
            ];
            $i--;
        }

        $true = $this->publisher->work($messages);

        // CONFIRMED: 4000 + 9 messages where published to the queue successfuly.
        // Note that ConsumerTest will fail if the messages were not published
        $this->assertTrue($true);
    }
}
