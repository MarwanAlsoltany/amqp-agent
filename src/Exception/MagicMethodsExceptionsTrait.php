<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Exception;

use MAKS\AmqpAgent\Helper\ArrayProxy;
use MAKS\AmqpAgent\Exception\PropertyDoesNotExistException;
use MAKS\AmqpAgent\Exception\MethodDoesNotExistException;

/**
 * A trait to throw exceptions on calls to magic methods.
 * @since 1.2.0
 */
trait MagicMethodsExceptionsTrait
{
    /**
     * Throws an exception when trying to get a class member via public property assignment notation.
     * @param string $property Property name.
     * @return void
     * @throws PropertyDoesNotExistException
     */
    public function __get(string $property)
    {
        throw new PropertyDoesNotExistException(
            sprintf(
                'The requested property with the name "%s" does not exist!',
                $property
            )
        );
    }

    /**
     * Throws an exception when trying to set a class member via public property assignment notation.
     * @param string $property Property name.
     * @param array $value Property value.
     * @return void
     * @throws PropertyDoesNotExistException
     */
    public function __set(string $property, $value)
    {
        throw new PropertyDoesNotExistException(
            sprintf(
                'A property with the name "%s" is immutable or does not exist!',
                $property
            )
        );
    }

    /**
     * Throws an exception for calls to undefined methods.
     * @param string $method Function name.
     * @param array $parameters Function arguments.
     * @return mixed
     * @throws MethodDoesNotExistException
     */
    public function __call(string $method, $parameters)
    {
        throw new MethodDoesNotExistException(
            sprintf(
                'The called method "%s" with the parameter(s) "%s" does not exist!',
                $method,
                ArrayProxy::castArrayToString($parameters)
            )
        );
    }

    /**
     * Throws an exception for calls to undefined static methods.
     * @param string $method Function name.
     * @param array $parameters Function arguments.
     * @return mixed
     * @throws MethodDoesNotExistException
     */
    public static function __callStatic(string $method, $parameters)
    {
        throw new MethodDoesNotExistException(
            sprintf(
                'The called static method "%s" with the parameter(s) "%s" does not exist',
                $method,
                ArrayProxy::castArrayToString($parameters)
            )
        );
    }
}
