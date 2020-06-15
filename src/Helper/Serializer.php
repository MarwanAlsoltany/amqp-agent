<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */


namespace MAKS\AmqpAgent\Helper;

use Exception;
use MAKS\AmqpAgent\Exception\SerializerViolationException;

/**
 * A flexible serializer to be used in conjuction with the workers.
 * @since 1.0.0
 */
class Serializer
{
    /**
     * The default data the serializer works with if none was provided.
     * @var null
     */
    public const DEFAULT_DATA = null;

    /**
     * The default type the serializer works with if none was provided.
     * @var string
     */
    public const DEFAULT_TYPE = 'JSON';


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
     * Serializer object constuctor.
     * @param mixed $data [optional] The data to serialize. Defaults to null.
     * @param string $type [optional] The type of serialization. Defaults to JSON.
     */
    public function __construct($data = self::DEFAULT_DATA, string $type = self::DEFAULT_TYPE)
    {
        $this->data = $data;
        $this->type = strtoupper($type);
    }

    /**
     * Executes when calling the class like a function.
     * @param mixed $data The data to (un)serialize.
     * @param string [optional] $type The type of (un)serialization. Defaults to JSON.
     * @return mixed Serialized or unserialized data depending on the passed parameters.
     */
    public function __invoke($data, ?string $type = self::DEFAULT_TYPE)
    {
        $this->data = $data;
        $this->type = strtoupper($type);

        try {
            return is_string($data) ? $this->unserialize() : $this->serialize();
        } catch (Exception $error) {
            $dataType = gettype($data);
            throw new SerializerViolationException(
                "The data passed to the serializer (data-type: {$dataType}) couldn't be processed!",
                (int)$error->getCode(),
                $error
            );
        }
    }


    /**
     * Serializes the passed or registered data. When no parameters are passed, it uses the registered ones.
     * @param mixed [optional] $data The data to serialize.
     * @param string [optional] $type The type of serialization.
     * @return string|false Returns false on failure.
     * @throws SerializerViolationException
     */
    public function serialize($data = null, ?string $type = null): string
    {
        if (null !== $data) {
            $this->data = $data;
        }
        if (null !== $type) {
            $this->type = strtoupper($type);
        }
        if ($this->type === 'PHP') {
            return serialize($this->data);
        } elseif ($this->type === 'JSON') {
            return json_encode($this->data);
        }
        throw new SerializerViolationException("\"{$this->type}\" is unsupported serilaization type. Supported types are (JSON, PHP)!");
    }

    /**
     * Unserializes the passed or registered data. When no parameters are passed, it uses the registered ones.
     * @param string [optional] $data The data to unserialize.
     * @param string [optional] $type The type of unserialization.
     * @return mixed A PHP type on success or false or null on failure.
     * @throws SerializerViolationException
     */
    public function unserialize(?string $data = null, ?string $type = null)
    {
        if (null !== $data) {
            $this->data = $data;
        }
        if (null !== $type) {
            $this->type = strtoupper($type);
        }
        if ($this->type === 'PHP') {
            return unserialize($this->data);
        } elseif ($this->type === 'JSON') {
            return json_decode($this->data, true);
        }
        throw new SerializerViolationException("\"{$this->type}\" is unsupported unserilaization type. Supported types are (JSON, PHP)!");
    }

    /**
     * Registers the passed data in the class.
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
     * Registers the passed data in the class.
     * @param string $type The type wished to be registered.
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = strtoupper($type);
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
     * Alias for self::serialize that does not accept any parameters (works with currently registered parameters).
     * @return string The serialized data.
     */
    public function getSerialized(): string
    {
        return $this->serialize();
    }

    /**
     * Alias for self::unserialize that does not accept any parameters (works with currently registered parameters).
     * @return mixed The unserialized data.
     */
    public function getUnserialized()
    {
        return $this->unserialize();
    }
}
