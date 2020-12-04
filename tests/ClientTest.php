<?php

namespace MAKS\AmqpAgent\Tests;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Client;
use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\Worker\Publisher;
use MAKS\AmqpAgent\Worker\Consumer;
use MAKS\AmqpAgent\RPC\ServerEndpoint;
use MAKS\AmqpAgent\RPC\ClientEndpoint;
use MAKS\AmqpAgent\Helper\Serializer;
use MAKS\AmqpAgent\Helper\Logger;
use MAKS\AmqpAgent\Exception\AmqpAgentException;

class ClientTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Client
     */
    private $agent;

    public function setUp(): void
    {
        parent::setUp();
        $this->config = new Config();
        $this->agent = new Client($this->config);
    }

    public function tearDown(): void
    {
        parent::setUp();
        unset($this->config);
        unset($this->agent);
    }

    public function testAmqpAgentExceptionViaConstructorIsRaisedWhenUnSupportedArgumentIsProvided()
    {
        $this->expectException(AmqpAgentException::class);
        $client = new Client(null);
    }

    public function testInstantiatingAClientWithAPathToConfigFileAndGetingConfigInstanceBack()
    {
        $client = new Client(Config::DEFAULT_CONFIG_FILE_PATH);
        $this->assertInstanceOf(Config::class, $client->getConfig());
    }

    public function testGetPublisherInstance()
    {
        $this->assertInstanceOf(Publisher::class, $this->agent->getPublisher());
        $this->assertInstanceOf(Publisher::class, $this->agent->publisher);
    }

    public function testGetConsumerInstance()
    {
        $this->assertInstanceOf(Consumer::class, $this->agent->getConsumer());
        $this->assertInstanceOf(Consumer::class, $this->agent->consumer);
    }

    public function testGetServerEndpointInstance()
    {
        $this->assertInstanceOf(ServerEndpoint::class, $this->agent->getServerEndpoint());
        $this->assertInstanceOf(ServerEndpoint::class, $this->agent->serverEndpoint);
    }

    public function testGetClientEndpointInstance()
    {
        $this->assertInstanceOf(ClientEndpoint::class, $this->agent->getClientEndpoint());
        $this->assertInstanceOf(ClientEndpoint::class, $this->agent->clientEndpoint);
    }

    public function testGetSerializerInstance()
    {
        $this->assertInstanceOf(Serializer::class, $this->agent->getSerializer());
        $this->assertInstanceOf(Serializer::class, $this->agent->serializer);
    }

    public function testGetLoggerInstance()
    {
        $this->assertInstanceOf(Logger::class, $this->agent->getLogger());
        $this->assertInstanceOf(Logger::class, $this->agent->logger);
    }

    public function testGetInstanceByName()
    {
        $this->assertInstanceOf(Logger::class, $this->agent->get('logger'));
        $this->assertInstanceOf(Logger::class, $this->agent->logger);
    }

    public function testGetInstanceRaisesExceptionIfMemberCouldNotBeFetched()
    {
        $this->expectException(AmqpAgentException::class);
        $error = $this->agent->get('worker');
    }

    public function testGettableReturnsAnArrayWithExpectedValues()
    {
        $array = $this->agent->gettable();
        $this->assertIsArray($array);
        $this->assertTrue(in_array('config', $array));
    }
}
