<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent;

use MAKS\AmqpAgent\Config;
use MAKS\AmqpAgent\Worker\Publisher;
use MAKS\AmqpAgent\Worker\Consumer;
use MAKS\AmqpAgent\Helper\Serializer;
use MAKS\AmqpAgent\Helper\Logger;
use MAKS\AmqpAgent\Exception\AmqpAgentException;

/**
 * A class returns everything AMQP Agent has to offer. A simple service container so to say.
 * @since 1.0.0
 * @api
 */
class Client
{
    /**
     * An instance of the configuration object.
     * @var Config
     */
    protected $config;

    /**
     * An instance of the Publisher worker class.
     * @var Publisher
     */
    protected $publisher;

    /**
     * An instance of the Consumer worker class.
     * @var Consumer
     */
    protected $consumer;

    /**
     * An instance of the Serializer class.
     * @var Serializer
     */
    protected $serializer;

    /**
     * An instance of the Logger class.
     * @var Logger
     */
    protected $logger;


    /**
     * Client object constuctor.
     * @param Config|string $config An instance of the Config class or a path to a config file.
     * @throws AmqpAgentException
     */
    public function __construct($config)
    {
        if ($config instanceof Config) {
            $this->config = $config;
        } elseif (is_string($config) && strlen(trim($config)) > 0) {
            $this->config = new Config($config);
        } else {
            throw new AmqpAgentException(
                'A Config instance or a valid path to a config file must be specifed.'
            );
        }
    }

    /**
     * Gets a class member via public property access notation.
     * @param string $member Property name.
     * @return mixed
     */
    public function __get(string $member)
    {
        // using $this->get() to reuse the logic in class methods.
        return $this->get($member);
    }


    /**
     * Returns an instance of class by its name.
     * @param string $member Property name.
     * @return Config|Publisher|Consumer|Serializer|Logger
     * @throws AmqpAgentException
     */
    public function get(string $member)
    {
        $method = __FUNCTION__ . ucfirst(strtolower($member));

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new AmqpAgentException(
            "The requested member with the name \"{$member}\" does not exist!"
        );
    }

    /**
     * Returns an instance of the Publisher class.
     * @return Publisher
     * @api
     */
    public function getPublisher(): Publisher
    {
        if (!isset($this->publisher)) {
            $this->publisher = new Publisher(
                $this->config->connectionOptions,
                $this->config->channelOptions,
                $this->config->queueOptions,
                $this->config->exchangeOptions,
                $this->config->bindOptions,
                $this->config->messageOptions,
                $this->config->publishOptions
            );
        }

        return $this->publisher;
    }

    /**
     * Returns an instance of the Consumer class.
     * @return Consumer
     */
    public function getConsumer(): Consumer
    {
        if (!isset($this->consumer)) {
            $this->consumer = new Consumer(
                $this->config->connectionOptions,
                $this->config->channelOptions,
                $this->config->queueOptions,
                $this->config->qosOptions,
                $this->config->waitOptions,
                $this->config->consumeOptions
            );
        }

        return $this->consumer;
    }

    /**
     * Returns an instance of the Serializer class.
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        if (!isset($this->serializer)) {
            $this->serializer = new Serializer();
        }

        return $this->serializer;
    }

    /**
     * Returns an instance of the Logger class. Filename and directory must be set through setters.
     * @return Logger
     */
    public function getLogger(): Logger
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger(null, null);
        }

        return $this->logger;
    }

    /**
     * Returns the currently used config object.
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
