<?php

namespace MAKS\AmqpAgent\Tests\Worker;

use MAKS\AmqpAgent\Config\AmqpAgentParameters;
use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Worker\AbstractWorker;
use MAKS\AmqpAgent\Exception\MethodDoesNotExistException;
use MAKS\AmqpAgent\Exception\PropertyDoesNotExistException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;

final class AbstractWorkerMock extends AbstractWorker
{
    // Mock
}

class AbstractWorkerTest extends TestCase
{
    /**
     * @var AbstractWorker
     */
    private $worker;

    public function setUp(): void
    {
        parent::setUp();
        $this->worker = new AbstractWorkerMock();
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->worker);
    }

    public function testMethodDoesNotExistExceptionIsRaisedWhenCallingANonExistenceMethod()
    {
        $this->expectException(MethodDoesNotExistException::class);
        $this->worker->aNonExistenceMethod();
    }

    public function testMethodDoesNotExistExceptionIsRaisedWhenCallingANonExistenceStaticMethod()
    {
        $this->expectException(MethodDoesNotExistException::class);
        AbstractWorkerMock::aNonExistenceMethod();
    }

    public function testPropertyDoesNotExistExceptionIsRaisedViaPublicAccessNotation()
    {
        $this->expectException(PropertyDoesNotExistException::class);
        $this->worker->aNonExistenceProperty;
    }

    public function testPropertyDoesNotExistExceptionIsRaisedViaPublicAssignmentNotation()
    {
        $this->expectException(PropertyDoesNotExistException::class);
        $this->worker->aNonExistenceProperty = [];
    }

    public function testShutdownMethodRaisesAnExceptionIfUnexpectedParameterIsPassed()
    {
        $this->expectException(AMQPInvalidArgumentException::class);
        AbstractWorkerMock::shutdown(true, false, null);
    }

    public function testGetAndSetPropertyViaPublicAccessNotation()
    {
        $this->worker->queueOptions = [
            'queue' => 'maks.amqp.agent.new.queue'
        ];
        $this->assertEquals('maks.amqp.agent.new.queue', $this->worker->queueOptions['queue']);
    }

    public function testConnectReturnsAnAMQPStreamConnection()
    {
        $this->worker->connect();
        $this->assertInstanceOf(AMQPStreamConnection::class, $this->worker->connection);
        $this->assertInstanceOf(AMQPChannel::class, $this->worker->channel);
    }

    public function testDisconnectUnsetsTheClassProperties()
    {
        $this->worker->disconnect();
        $this->assertEmpty($this->worker->connection);
        $this->assertEmpty($this->worker->channel);
    }

    public function testReconnectRepopulatesClassPropertiesAndReturnsSelf()
    {
        $worker = $this->worker->reconnect();
        $this->assertNotNull($this->worker->connection);
        $this->assertNotNull($this->worker->channel);
        $this->assertEquals($worker, $this->worker);
    }

    public function testQueueReturnsSelf()
    {
        $worker = $this->worker->connect()->queue();
        $this->assertEquals($worker, $this->worker);
    }

    public function testArgumentsReturnsAMQPTable()
    {
        $arguments = $this->worker->arguments([]);
        $this->assertInstanceOf(AMQPTable::class, $arguments);
    }

    public function testGetConnectionReturnsTheDefaultConnection()
    {
        $connection = $this->worker->connect()->getConnection();
        $this->assertEquals($connection, $this->worker->connection);
    }

    public function testGetNewConnectionReturnsANewConnectionAndUsesOverrideParameters()
    {
        $connection = $this->worker->connect()->getNewConnection([
            'host' => 'localhost'
        ]);
        $this->assertInstanceOf(AMQPStreamConnection::class, $connection);
        $this->assertNotEquals($connection, $this->worker->connection);
    }

    public function testSetConnectionSetsAnotherConnectionAsDefaultAndReturnsSelf()
    {
        $oldConnection = $this->worker->connect()->getConnection();
        $newConnection = $this->worker->connect()->getNewConnection();
        $worker = $this->worker->setConnection($newConnection);
        $this->assertEquals($worker, $this->worker);
        $this->assertNotEquals($oldConnection, $newConnection);
        $this->assertEquals($this->worker->connect()->getConnection(), $newConnection);
    }

    public function testGetChannelReturnsTheDefaultChannel()
    {
        $channel = $this->worker->connect()->getChannel();
        $this->assertEquals($channel, $this->worker->channel);
    }

    public function testGetNewChannelReturnsANewChannelAndUsesOverrideParameters()
    {
        $oldChannel = $this->worker->connect()->getChannel();
        $newChannel = $this->worker->connect()->getNewChannel();
        $worker = $this->worker->setChannel($newChannel);
        $this->assertEquals($worker, $this->worker);
        $this->assertNotEquals($oldChannel, $newChannel);
        $this->assertEquals($this->worker->connect()->getChannel(), $newChannel);
    }

    public function testSetChannelSetsAnotherChannelAsDefaultAndReturnsSelf()
    {
        /** @var AMQPChannel */
        $newChannel = $this->worker->connect()->getNewChannel(['channel_id' => 1968]);
        $this->assertInstanceOf(AMQPChannel::class, $newChannel);
        $this->assertEquals(1968, $newChannel->getChannelId());
        $this->assertNotEquals($newChannel, $this->worker->channel);
    }

    public function testGetChannelByIdReturnsAChannelWithThatId()
    {
        /** @var AMQPChannel */
        $newChannel1 = $this->worker->connect()->getNewChannel(['channel_id' => 7]);
        $newChannel2 = $this->worker->getChannelById(7);
        $this->assertEquals($newChannel1, $newChannel2);
    }

    public function testGetChannelByIdReturnsNullForRandomChannelId()
    {
        $newChannel = $this->worker->connect()->getChannelById(rand(1972, 1993));
        $this->assertNotInstanceOf(AMQPChannel::class, $newChannel);
        $this->assertEmpty($newChannel);
    }

    public function testWorkerShutdownFunction()
    {
        $worker = $this->worker->connect();
        $connection = $worker->getConnection();
        $channel = $worker->getChannel();
        $this->assertTrue($connection->isConnected());
        $this->assertTrue($channel->is_open());
        AbstractWorkerMock::shutdown($channel, $connection);
        $this->assertFalse($connection->isConnected());
        $this->assertFalse($channel->is_open());
    }

    public function testMutateClassMemberTakesEffectByRevertingPropertyToItsState()
    {
        $this->worker->connect();
        $this->worker->queue(['queue' => 'this.is.not.a.normal.queue.name']);
        $defaultQueueName = $this->worker->queueOptions['queue'];
        // Queue name should be reverted back to maks.amqp.agent.queue
        $this->assertEquals(AmqpAgentParameters::PREFIX . 'queue', $defaultQueueName);
    }

    public function testMakeCommandReturnsTheExpectedFormat()
    {
        $excpected = [
            '__COMMAND__' => [
                'test' => 'command',
                'flags' => [
                    'run' => true
                ]
            ]
        ];
        $actual = AbstractWorkerMock::makeCommand('test', 'command', ['run' => true], 'flags');
        $this->assertEquals(serialize($excpected), serialize($actual));
    }

    public function testIsCommandChecksIfThePassedArrayIsACommand()
    {
        $isCommand = AbstractWorkerMock::isCommand(AbstractWorkerMock::makeCommand('test', 'command'));
        $isNotCommand = AbstractWorkerMock::isCommand([]);
        $this->assertTrue($isCommand);
        $this->assertFalse($isNotCommand);
    }

    public function testHasCommandReturnsTrueIfCommandWasFoundOtherwiseFalse()
    {
        $command = AbstractWorkerMock::makeCommand('test', 'command');
        $truly = AbstractWorkerMock::hasCommand($command, 'test');
        $falsy = AbstractWorkerMock::hasCommand($command, 'test', 'COMMAND');
        $this->assertNotEquals($truly, $falsy);
    }

    public function testGetCommandReturnsTheParametersOrASubsetOfIt()
    {
        $command = AbstractWorkerMock::makeCommand('make', 'command', ['test' => true], 'flags');
        $truly = AbstractWorkerMock::getCommand($command, 'flags', 'test');
        $falsy = AbstractWorkerMock::getCommand($command, 'flags', 'TEST');
        $this->assertNotEquals($truly, $falsy);
    }
}
