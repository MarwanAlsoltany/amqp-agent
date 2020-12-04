<?php

namespace MAKS\AmqpAgent\Tests\RPC;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\RPC\ServerEndpoint;
use MAKS\AmqpAgent\Helper\Utility;
use MAKS\AmqpAgent\Exception\AmqpAgentException;
use MAKS\AmqpAgent\Exception\RPCEndpointException;

class ServerEndpointTest extends TestCase
{
    /**
     * @var ServerEndpoint
     */
    private $server;

    public function setUp(): void
    {
        parent::setUp();
        $this->server = new ServerEndpoint();
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->server);
    }

    public function testRespondMethodViaAliasMethodServe()
    {
        $queue = 'maks.amqp.agent.rpc.queue.server.respond.test';
        $message = 'close';

        // Starting a client to send requests
        $command = sprintf('php bin/endpoint client %s --quiet', $queue);
        Utility::execute($command, __DIR__, true);

        $this->server->on('response.after.send', function ($response, $server, $event) {
            if ($response->body == 'close') {
                return $server->disconnect();
            }
        });

        $this->server->connect();
        $request = $this->server->serve(null, $queue);
        $this->server->disconnect();

        $this->assertEquals($message, $request);
    }

    public function testRespondMethodRaisesAnExceptionIfTheServerIsNotConnectedYet()
    {
        $this->expectException(RPCEndpointException::class);
        $this->server->respond();
    }

    public function testAnExceptionIsRaisedIfPassedCallbackDoesNotReturnString()
    {
        $queue = 'maks.amqp.agent.rpc.queue.server.callback.exception.test';

        // Starting a client to send requests
        $command = sprintf('php bin/endpoint client %s --quiet', $queue);
        Utility::execute($command, __DIR__, true);

        $this->expectException(AmqpAgentException::class);

        $callback = function ($message) use ($queue) {
            $this->assertIsString($message->body);

            // Here starting a new Server to finish the pending request
            $command = sprintf('php bin/endpoint server %s --quiet', $queue);
            Utility::execute($command, __DIR__, true);

            return null;
        };

        $this->server->connect();
        $this->server->respond($callback, $queue);
        $this->server->disconnect();
    }
}
