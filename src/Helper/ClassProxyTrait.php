<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Helper;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionObject;
use ReflectionException;
use MAKS\AmqpAgent\Exception\AmqpAgentException;

/**
 * A trait containing methods for proxy methods calling, properties manipulation, and class utilities.
 * @since 2.0.0
 */
trait ClassProxyTrait
{
    /**
     * Calls a private, protected, or public method on an object.
     * @param object $object Class instance.
     * @param string $method Method name.
     * @param mixed ...$arguments
     * @return mixed The function result, or false on error.
     * @throws AmqpAgentException On failure or if the called function threw an exception.
     */
    public static function callMethod($object, string $method, ...$arguments)
    {
        return call_user_func(
            Closure::bind(
                function () use ($object, $method, $arguments) {
                    try {
                        return call_user_func_array(
                            array(
                                $object,
                                $method
                            ),
                            $arguments
                        );
                    } catch (Exception $error) {
                        AmqpAgentException::rethrow($error, sprintf('%s::%s() failed!', static::class, __FUNCTION__));
                    }
                },
                null,
                $object
            )
        );
    }

    /**
     * Gets a private, protected, or public property (default, static, or constant) of an object.
     * @param object $object Class instance.
     * @param string $property Property name.
     * @return mixed The property value.
     * @throws AmqpAgentException On failure.
     */
    public static function getProperty($object, string $property)
    {
        return call_user_func(
            Closure::bind(
                function () use ($object, $property) {
                    $return = null;
                    try {
                        $class = get_class($object);
                        if (defined($class . '::' . $property)) {
                            $return = constant($class . '::' . $property);
                        } elseif (isset($object::$$property)) {
                            $return = $object::$$property;
                        } elseif (isset($object->{$property})) {
                            $return = $object->{$property};
                        } else {
                            throw new Exception(
                                sprintf(
                                    'No default, static, or constant property with the name "%s" exists!',
                                    $property
                                )
                            );
                        }
                    } catch (Exception $error) {
                        AmqpAgentException::rethrow($error, sprintf('%s::%s() failed!', static::class, __FUNCTION__));
                    }
                    return $return;
                },
                null,
                $object
            )
        );
    }

    /**
     * Sets a private, protected, or public property (default or static) of an object.
     * @param object $object Class instance.
     * @param string $property Property name.
     * @param string $value Property value.
     * @return mixed The new property value.
     * @throws AmqpAgentException On failure.
     */
    public static function setProperty($object, string $property, $value)
    {
        return call_user_func(
            Closure::bind(
                function () use ($object, $property, $value) {
                    $return = null;
                    try {
                        if (isset($object::$$property)) {
                            $return = $object::$$property = $value;
                        } elseif (isset($object->{$property})) {
                            $return = $object->{$property} = $value;
                        } else {
                            throw new Exception(
                                sprintf(
                                    'No default or static property with the name "%s" exists!',
                                    $property
                                )
                            );
                        }
                    } catch (Exception $error) {
                        AmqpAgentException::rethrow($error, sprintf('%s::%s() failed!', static::class, __FUNCTION__));
                    }
                    return $return;
                },
                null,
                $object
            )
        );
    }

    /**
     * Returns a reflection class instance on a class.
     * @param object|string $class Class instance or class FQN.
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public static function reflectOnClass($class)
    {
        return new ReflectionClass($class);
    }

    /**
     * Returns a reflection object instance on an object.
     * @param object $object Class instance.
     * @return ReflectionObject
     */
    public static function reflectOnObject($object)
    {
        return new ReflectionObject($object);
    }

    /**
     * Tries to cast an object into a new class. Similar classes work best.
     * @param object $fromObject Class instance.
     * @param string $toClass Class FQN.
     * @return object
     * @throws AmqpAgentException When passing a wrong argument or on failure.
     */
    public static function castObjectToClass($fromObject, string $toClass)
    {
        if (!is_object($fromObject)) {
            throw new AmqpAgentException(
                sprintf(
                    'The first parameter must be an instance of class, a wrong parameter with (data-type: %s) was passed instead.',
                    gettype($fromObject)
                )
            );
        }

        if (!class_exists($toClass)) {
            throw new AmqpAgentException(
                sprintf(
                    'Unknown class: %s.',
                    $toClass
                )
            );
        }

        try {
            $toClass = new $toClass();

            $toClassReflection = self::reflectOnObject($toClass);
            $fromObjectReflection = self::reflectOnObject($fromObject);

            $fromObjectProperties = $fromObjectReflection->getProperties();

            foreach ($fromObjectProperties as $fromObjectProperty) {
                $fromObjectProperty->setAccessible(true);
                $name = $fromObjectProperty->getName();
                $value = $fromObjectProperty->getValue($fromObject);

                if ($toClassReflection->hasProperty($name)) {
                    $property = $toClassReflection->getProperty($name);
                    $property->setAccessible(true);
                    $property->setValue($toClass, $value);
                } else {
                    try {
                        self::setProperty($toClass, $name, $value);
                    } catch (Exception $e) {
                        // This exception means target object has a __set()
                        // magic method that prevents setting the property.
                    }
                }
            }

            return $toClass;
        } catch (Exception $error) {
            AmqpAgentException::rethrow($error, sprintf('%s::%s() failed!', static::class, __FUNCTION__));
        }
    }
}
