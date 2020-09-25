<?php

namespace MAKS\AmqpAgent\Test\Worker;

use MAKS\AmqpAgent\TestCase;
use MAKS\AmqpAgent\Worker\Publisher;
use MAKS\AmqpAgent\Worker\PublisherSingleton;
use MAKS\AmqpAgent\Worker\AbstractWorkerSingleton;

final class PublisherWithConstantMock extends Publisher
{
    public const TEST_CONSTANT = 'TEST';
}
final class PublisherSingletonWithConstantMock extends AbstractWorkerSingleton
{
    public function __construct()
    {
        $this->worker = new PublisherWithConstantMock();
    }
}
class PublisherSingletonTest extends TestCase
{
    /**
     * @var PublisherSingleton
     */
    private $publisher;

    public function setUp(): void
    {
        parent::setUp();
        $this->publisher = PublisherSingleton::getInstance();
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->publisher);
    }

    /**
     * The tests bellow apply for AbstractWorkerSingleton, PublisherSingleton, and ConsumerSingleton
     */

    public function testSingleton()
    {
        $publisher = PublisherSingleton::getInstance();
        $this->assertEquals($publisher, $this->publisher);
    }

    public function testSingletonGetInstanceInstantiatesClassWithTheSuppliedParameters()
    {
        $publisher = PublisherSingleton::getInstance([], [], [
            'queue' => 'this.is.not.the.normal.queue.name'
        ]);

        $this->assertEquals('this.is.not.the.normal.queue.name', $publisher->queueOptions['queue']);
    }

    public function testSingletonInstanceGetPropertyViaPublicAccessNotation()
    {
        $this->publisher->connect();
        $channel = $this->publisher->channel;
        $this->assertEquals($this->publisher->getChannel(), $channel);
    }

    public function testSingletonInstanceGetPropertyViaPublicAssignmentNotation()
    {
        // disconnect here to insure that the connection
        // is new one and not an old one from the singleton.
        $this->publisher->disconnect();
        $this->publisher->channelOptions = [
            'channel_id' => 1947
        ];
        $this->publisher->connect();
        $channelId = $this->publisher->getChannel()->getChannelId();
        $this->assertEquals(1947, $channelId);
    }

    public function testSingletonInstanceWhenCallingStaticMethod()
    {
        $publisher = $this->publisher->connect();
        $connection = $publisher->getConnection();
        $channel = $publisher->getChannel();
        $this->assertTrue($connection->isConnected());
        $this->assertTrue($channel->is_open());
        PublisherSingleton::shutdown($channel, $connection);
        $this->assertFalse($connection->isConnected());
        $this->assertFalse($channel->is_open());
    }

    public function testSingletonInstanceRetrievingStaticOrConstProperty()
    {
        $commandSyntax = ['ABC' => 'D'];
        $this->assertEquals(Publisher::$commandPrefix, $this->publisher->commandPrefix);
        $this->publisher->commandSyntax = $commandSyntax;
        $this->assertEquals($commandSyntax, $this->publisher->commandSyntax);
        $this->assertEquals(PublisherWithConstantMock::TEST_CONSTANT, PublisherSingletonWithConstantMock::getInstance()->TEST_CONSTANT);
    }
}
