<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent;

use Exception;
use MAKS\AmqpAgent\Helper\Utility;
use MAKS\AmqpAgent\Exception\ConfigFileNotFoundException;

/**
 * A class that turns the configuration file into an object.
 *
 * Example:
 * ```
 * $config = new Config('path/to/some/config-file.php'); // specific config
 * $config = new Config(); // default config
 * ```
 *
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
     * Configuration file path.
     * @var string
     */
    private $configPath;


    /**
     * Config object constructor.
     * @param string|null $configPath [optional] The path to AMQP Agent configuration file.
     */
    public function __construct(?string $configPath = null)
    {
        $configFile = realpath($configPath ?? self::DEFAULT_CONFIG_FILE_PATH);

        if (!file_exists($configFile)) {
            throw new ConfigFileNotFoundException(
                "AMQP Agent configuration file cloud not be found, check if the given path \"{$configPath}\" exists."
            );
        }

        $this->config = include($configFile);
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
     * Sets the the given key in the configuration array via public property assignment notation.
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
     * Checks wether a value exists in the configuration array via dot-notation representation.
     * @since 1.2.2
     * @param string $key The dotted key representation.
     * @return bool True if key is set otherwise false.
     */
    public function has(string $key): bool
    {
        $value = Utility::getArrayValueByKey($this->config, $key, null);

        return isset($value);
    }

    /**
     * Gets a value of a key from the configuration array via dot-notation representation.
     * @since 1.2.2
     * @param string $key The dotted key representation.
     * @return mixed The requested value or null.
     */
    public function get(string $key)
    {
        $value = Utility::getArrayValueByKey($this->config, $key);

        return $value;
    }

    /**
     * Sets a value of a key from the configuration array via dot-notation representation.
     * @since 1.2.2
     * @param string $key The dotted key representation.
     * @param string $value The value to set.
     * @return self
     */
    public function set(string $key, $value)
    {
        Utility::setArrayValueByKey($this->config, $key, $value);

        return $this;
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
            $this->configPath = $configPath;

            $this->repair();
        } catch (Exception $error) {
            throw new ConfigFileNotFoundException(
                "Something went wrong when trying to include the file and rebuild the configuration, check if the given path \"{$configPath}\" exists.",
                (int)$error->getCode(),
                $error
            );
        }

        return $this;
    }
}
