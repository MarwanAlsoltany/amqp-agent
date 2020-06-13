<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent;

use Exception;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use MAKS\AmqpAgent\Exception\ConfigFileNotFoundException;

/**
 * A class that turns the configuration file into an object.
 * @since 1.0.0
 * @property array $connectionOptions
 * @property array $channelOptions
 * @property array $queueOptions
 * @property array $exchangeOptions
 * @property array $bindOptions
 * @property array $qosOptions
 * @property array $waitOptions
 * @property array $messageOptions
 * @property array $publishOptions
 * @property array $consumeOptions
 */
final class Config
{
    /**
     * The default name of the configuration file.
     * @var string
     */
    public const DEFAULT_CONFIG_FILE_NAME = 'maks-amqp-agent-config';

    /**
     * The default name of the configuration file.
     * @var string
     */
    public const DEFAULT_CONFIG_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . self::DEFAULT_CONFIG_FILE_NAME . '.php';

    /**
     * The multidimensional configuration array.
     * @var array
     */
    private $config;

    /**
     * A flat version of the configuration array.
     * @var array
     */
    private $configFlat;

    /**
     * Configuration file path.
     * @var string
     */
    private $configPath;


    /**
     * Config object constroctor.
     * @param string|null $configPath [optional] The path to AMQP Agent configuration file.
     */
    public function __construct(?string $configPath = null)
    {
        $configFile = $configPath ? $configPath : self::DEFAULT_CONFIG_FILE_PATH;

        if (!file_exists($configFile)) {
            throw new ConfigFileNotFoundException(
                "AMQP Agent configurartion file cloud not be found, check if the given path \"{$configPath}\" exists."
            );
        }

        $this->config = include($configFile);
        $this->configFlat = array();
        $this->configPath = $configFile;
        $this->repair();
    }

    /**
     * Gets the the given key from the configuration array via public property access notation.
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->config[$key];
    }

    /**
     * Sets the the given key in the configuration array via public property assginment notation.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * Returns config file path if the object was casted to a string.
     * @return string
     */
    public function __toString()
    {
        return $this->configPath;
    }


    /**
     * Repairs the config array if first-level of the passed array does not have all keys.
     * @return void
     */
    private function repair(): void
    {
        $config = require(self::DEFAULT_CONFIG_FILE_PATH);

        foreach ($config as $key => $value) {
            if (!array_key_exists($key, $this->config)) {
                $this->config[$key] = [];
            }
        }

        unset($config);
    }

    /**
     * Gets a value of a key from the configuation array. Use with caution.
     * Please note that this function returns the last occurence of a key.
     * That's why it's not recommended to rely on the values provided by it.
     * @deprecated 1.0.0 Use public property access notation instead.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (sizeof($this->configFlat) === 0) {
            $config = new RecursiveArrayIterator($this->config);
            $configFlat = new RecursiveIteratorIterator($config);
            foreach ($configFlat as $key => $value) {
                $this->configFlat[$key] = $value;
            }
        }

        return $this->configFlat[$key];
    }

    /**
     * Returns the default configuration array.
     * @return array
     */
    public function getDefaultConfig(): array
    {
        return include(self::DEFAULT_CONFIG_FILE_PATH);
    }

    /**
     * Returns the current configuration array.
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Sets a new configuration array to be used instead of the current and generates a new flat version of it.
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        $this->configFlat = [];
        $this->repair();
        return $this;
    }

    /**
     * Returns the path of the configuration file.
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * Sets the path of the configuration file and rebuilds the internal state of the object.
     * @param string $configPath
     * @return self
     */
    public function setConfigPath(string $configPath): self
    {
        try {
            $this->config = include($configPath);
            $this->configFlat = [];
            $this->configPath = $configPath;
            $this->repair();
        } catch (Exception $error) {
            throw new ConfigFileNotFoundException(
                "Something went wrong when trying to include the file and rebuild the confguration, check if the given path \"{$configPath}\" exists.",
                (int)$error->getCode(),
                $error
            );
        }

        return $this;
    }
}
