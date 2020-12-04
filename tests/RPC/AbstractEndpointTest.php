<?php

namespace MAKS\AmqpAgent\Tests\RPC;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Tests\Mocks\AbstractEndpointMock;
use MAKS\AmqpAgent\RPC\AbstractEndpoint;
use MAKS\AmqpAgent\Helper\ClassProxy;
use MAKS\AmqpAgent\Exception\RPCEndpointException;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class AbstractEndpointTest extends TestCase
{
    /**
     * @var AbstractEndpoint
     */
    private $endpoint;

    public function setUp(): void
    {
        parent::setUp();
        $this->endpoint = new AbstractEndpointMock();
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->endpoint);
    }

    public function testPingConnectMethodReturnsSelf()
    {
        $endpoint = $this->endpoint->connect();

        $this->assertInstanceOf(AbstractEndpoint::class, $endpoint);

        $this->endpoint->disconnect();
    }

    public function testPingConnectMethodRaisesAnExceptionIfAConnectionIsOpenAlready()
    {
        $this->endpoint->connect();
        $this->expectException(RPCEndpointException::class);
        $this->endpoint->connect([
            'host' => '127.0.0.1'
        ], 'failed.queue');
    }

    public function testGetConnectionMethodReturnsAMQPStreamConnection()
    {
        $endpoint = $this->endpoint->connect();

        $this->assertInstanceOf(AMQPStreamConnection::class, $endpoint->getConnection());

        $this->endpoint->disconnect();
    }

    public function testPingMethodWhenTheEndpointIsConnected()
    {
        $this->endpoint->connect();
        $ping = $this->endpoint->ping();

        $this->assertIsFloat($ping);

        $this->endpoint->disconnect();
    }

    public function testPingMethodWhenTheEndpointIsNotConnected()
    {
        $ping = $this->endpoint->ping();

        $this->assertIsFloat($ping);

        $this->endpoint->disconnect();
    }

    public function testPingMethodRaisesAnExceptionIfAnErrorOccurred()
    {
        $this->endpoint->connect();

        // Making the connection act as connected although it's not
        /** @var AMQPStreamConnection */
        $connection = ClassProxy::get($this->endpoint, 'connection');
        $connection->close();
        ClassProxy::set($connection, 'is_connected', true);

        $this->expectException(\Exception::class);
        $this->endpoint->ping();

        $this->endpoint->disconnect();
    }

    public function testOnMethodExecutesACallbackSuccessfully()
    {
        $this->endpoint
            ->on(
                'connection.after.open',
                function ($connection, $endpoint, $event) {
                    $this->assertInstanceOf(AMQPStreamConnection::class, $connection);
                    $this->assertInstanceOf(AbstractEndpoint::class, $endpoint);
                    $this->assertEquals('connection.after.open', $event);
                }
            )
            ->on(
                'connection.after.close',
                function ($connection, $endpoint, $event) {
                    $this->assertFalse($connection->isConnected());
                }
            )
            ->connect()
            ->disconnect();
    }
}
