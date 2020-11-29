<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */


namespace MAKS\AmqpAgent\Helper;

use Exception;
use Closure;
use MAKS\AmqpAgent\Exception\SerializerViolationException;

/**
 * A flexible serializer to be used in conjunction with the workers.
 * @since 1.0.0
 */
class Serializer
{
    /**
     * The JSON serialization type constant.
     * @var string
     */
    public const TYPE_JSON = 'JSON';

    /**
     * The PHP serialization type constant.
     * @var string
     */
    public const TYPE_PHP = 'PHP';

    /**
     * The default data the serializer works with if none was provided.
     * @var null
     */
    public const DEFAULT_DATA = null;

    /**
     * The default type the serializer works with if none was provided.
     * @var string
     */
    public const DEFAULT_TYPE = self::TYPE_JSON;

    /**
     * The default strict value the serializer works with if none was provided.
     * @var bool
     */
    public const DEFAULT_STRICT = true;

    /**
     * The supported serialization types.
     * @var array
     */
    protected const SUPPORTED_TYPES = [self::TYPE_JSON, self::TYPE_PHP];


    /**
     * The current data the serializer has.
     * @var mixed
     */
    protected $data;

    /**
     * The current type the serializer uses.
     * @var string
     */
    protected $type;

    /**
     * The current strict value the serializer works with.
     * @var bool
     */
    protected $strict;

    /**
     * The result of the last (un)serialization operation.
     * @var mixed
     */
    protected $result;


    /**
     * Serializer object constructor.
     * @param mixed $data [optional] The data to (un)serialize. Defaults to null.
     * @param string $type [optional] The type of (un)serialization. Defaults to JSON.
     * @param bool $strict [optional] Wether or not to assert that no errors have occurred while executing (un)serialization functions. Defaults to true.
     */
    public function __construct($data = null, ?string $type = null, ?bool $strict = null)
    {
        $this->setData($data ?? self::DEFAULT_DATA);
        $this->setType($type ?? self::DEFAULT_TYPE);
        $this->setStrict($strict ?? self::DEFAULT_STRICT);
    }

    /**
     * Executes when calling the class like a function.
     * @param mixed $data The data to (un)serialize.
     * @param string $type [optional] The type of (un)serialization. Defaults to JSON.
     * @param bool $strict [optional] Wether or not to assert that no errors have occurred while executing (un)serialization functions. Defaults to true.
     * @return mixed Serialized or unserialized data depending on the passed parameters.
     */
    public function __invoke($data, ?string $type = self::DEFAULT_TYPE, ?bool $strict = self::DEFAULT_STRICT)
    {
        $this->setData($data);
        $this->setType($type ?? self::DEFAULT_TYPE);
        $this->setStrict($strict ?? self::DEFAULT_STRICT);

        try {
            $this->result = is_string($data) ? $this->unserialize() : $this->serialize();
        } catch (Exception $error) {
            $dataType = gettype($data);
            throw new SerializerViolationException(
                sprintf(
                    'The data passed to the serializer (data-type: %s) could not be processed!',
                    $dataType
                ),
                (int)$error->getCode(),
                $error
            );
        }

        return $this->result;
    }


    /**
     * Serializes the passed or registered data. When no parameters are passed, it uses the registered ones.
     * @param mixed $data [optional] The data to serialize.
     * @param string $type [optional] The type of serialization.
     * @param bool $strict [optional] Wether or not to assert that no errors have occurred while executing serialization functions.
     * @return string|null A serialized representation of the passed or registered data or null on failure.
     * @throws SerializerViolationException
     */
    public function serialize($data = null, ?string $type = null, ?bool $strict = null): string
    {
        if (null !== $data) {
            $this->setData($data);
        }

        if (null !== $type) {
            $this->setType($type);
        }

        if (null !== $strict) {
            $this->setStrict($strict);
        }

        if (self::TYPE_PHP === $this->type) {
            $this->assertNoPhpSerializationError(function () {
                $this->result = serialize($this->data);
            });
        }

        if (self::TYPE_JSON === $this->type) {
            $this->assertNoJsonSerializationError(function () {
                $this->result = json_encode($this->data);
            });
        }

        return $this->result;
    }

    /**
     * Unserializes the passed or registered data. When no parameters are passed, it uses the registered ones.
     * @param string $data [optional] The data to unserialize.
     * @param string $type [optional] The type of unserialization.
     * @param bool $strict [optional] Wether or not to assert that no errors have occurred while executing unserialization functions.
     * @return mixed A PHP type on success or false or null on failure.
     * @throws SerializerViolationException
     */
    public function unserialize(?string $data = null, ?string $type = null, ?bool $strict = null)
    {
        if (null !== $data) {
            $this->setData($data);
        }

        if (null !== $type) {
            $this->setType($type);
        }

        if (null !== $strict) {
            $this->setStrict($strict);
        }

        if (self::TYPE_PHP === $this->type) {
            $this->assertNoPhpSerializationError(function () {
                $this->result = unserialize($this->data);
            });
        }

        if (self::TYPE_JSON === $this->type) {
            $this->assertNoJsonSerializationError(function () {
                $this->result = json_decode($this->data, true);
            });
        }

        return $this->result;
    }

    /**
     * Deserializes the passed or registered data. When no parameters are passed, it uses the registered ones.
     * @since 1.2.2 Alias for `self::unserialize()`.
     * @param string $data [optional] The data to unserialize.
     * @param string $type [optional] The type of unserialization.
     * @param bool $strict [optional] Wether or not to assert that no errors have occurred while executing unserialization functions.
     * @return mixed A PHP type on success or false or null on failure.
     * @throws SerializerViolationException
     */
    public function deserialize(?string $data = null, ?string $type = null, ?bool $strict = null)
    {
        return $this->unserialize($data, $type, $strict);
    }

    /**
     * Registers the passed data in the object.
     * @param mixed $data The data wished to be registered.
     * @return self
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns the currently registered data.
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Registers the passed type in the object.
     * @param string $type The type wished to be registered.
     * @return self
     */
    public function setType(string $type): self
    {
        $type = strtoupper($type);

        if (!in_array($type, static::SUPPORTED_TYPES)) {
            throw new SerializerViolationException(
                sprintf(
                    '"%s" is unsupported (un)serialization type. Supported types are: [%s]!',
                    $type,
                    implode(', ', static::SUPPORTED_TYPES)
                )
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Returns the currently registered type.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Registers the passed strict value in the object.
     * @since 1.2.2
     * @param bool $strict The strict value wished to be registered.
     * @return self
     */
    public function setStrict(bool $strict): self
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * Returns the currently registered strict value.
     * @since 1.2.2
     * @return bool
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Alias for `self::serialize()` that does not accept any parameters (works with currently registered parameters).
     * @return string The serialized data.
     */
    public function getSerialized(): string
    {
        return $this->serialize();
    }

    /**
     * Alias for `self::unserialize()` that does not accept any parameters (works with currently registered parameters).
     * @return mixed The unserialized data.
     */
    public function getUnserialized()
    {
        return $this->unserialize();
    }

    /**
     * Asserts that `serialize()` and/or `unserialize()` was executed successfully depending on strictness of the Serializer.
     * @since 1.2.2
     * @param $callback The (un)serialization callback to execute.
     * @return void
     */
    protected function assertNoPhpSerializationError(Closure $callback): void
    {
        $this->result = null;

        try {
            $callback();
        } catch (Exception $error) {
            if ($this->strict) {
                throw new SerializerViolationException(
                    sprintf(
                        'An error occurred while executing serialize() or unserialize(): %s',
                        (string)$error->getMessage()
                    ),
                    (int)$error->getCode(),
                    $error
                );
            }
        }
    }

    /**
     * Asserts that `json_encode()` and/or `json_decode()` was executed successfully depending on strictness of the Serializer.
     * @since 1.2.2
     * @param $callback The (un)serialization callback to execute.
     * @return void
     */
    protected function assertNoJsonSerializationError(Closure $callback): void
    {
        $this->result = null;

        try {
            $callback();
        } catch (Exception $error) {
            if ($this->strict) {
                throw new SerializerViolationException(
                    sprintf(
                        'An error occurred while executing json_encode() or json_decode(): %s',
                        (string)$error->getMessage()
                    ),
                    (int)$error->getCode(),
                    $error
                );
            }
            // JSON functions do not throw exceptions on PHP < v7.3.0
            // The code down below takes care of throwing the exception.
        }

        if ($this->strict && version_compare(PHP_VERSION, '7.3.0', '<')) {
            $errorCode = json_last_error();
            if ($errorCode !== JSON_ERROR_NONE) {
                $errorMessage = json_last_error_msg();
                throw new SerializerViolationException(
                    sprintf(
                        'An error occurred while executing json_encode() or json_decode(): %s',
                        $errorMessage
                    )
                );
            }
        }
    }
}
