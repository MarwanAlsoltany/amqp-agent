<?php

namespace MAKS\AmqpAgent\Tests\RPC;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\RPC\ClientEndpoint;
use MAKS\AmqpAgent\Helper\Utility;
use MAKS\AmqpAgent\Exception\AmqpAgentException;
use MAKS\AmqpAgent\Exception\RPCEndpointException;

class ClientEndpointTest extends TestCase
{
    /**
     * @var ClientEndpoint
     */
    private $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = new ClientEndpoint();
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->client);
    }

    public function testConnectMethodReturnsSelf()
    {
        $client = $this->client->connect();

        $this->assertInstanceOf(ClientEndpoint::class, $client);
    }

    public function testRequestMethodViaAliasMethodCall()
    {
        $queue = 'maks.amqp.agent.rpc.queue.client.request.test';
        $message = 'close';

        // Starting a server to send responses
        $command = sprintf('php bin/endpoint server %s --quiet', $queue);
        Utility::execute($command, __DIR__, true);

        $this->client->connect();
        $response = null;
        foreach (range(1, 100) as $i) {
            $this->client->call($i, $queue);
            if ($i == 100) {
                $response = $this->client->call($message, $queue);
            }
        }
        $this->client->disconnect();

        $this->assertEquals($message, $response);
    }

    public function testRequestMethodRaisesAnExceptionIfTheClientIsNotConnectedYet()
    {
        $this->expectException(RPCEndpointException::class);

        $this->client->request('this should fail!');
    }

    public function testAnExceptionIsRaisedIfCorrelationIdOfTheResponseIsNotTheSameAsTheRequest()
    {
        $queue = 'maks.amqp.agent.rpc.queue.correlation.id.exception.test';

        // Starting a server to send responses
        $command = sprintf('php bin/endpoint server %s --quiet', $queue);
        Utility::execute($command, __DIR__, true);

        // mimicking a wrong correlation id by manipulating the response via request lifecycle events
        $this->client->on('response.on.get', function ($response, $client, $event) {
            $response->set('correlation_id', uniqid());
        });

        $this->expectException(AmqpAgentException::class);

        $this->client->connect();
        $this->client->request('close', $queue);
        $this->client->disconnect();
    }
}
