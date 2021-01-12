<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Exception;

use Exception as CoreException;
use MAKS\AmqpAgent\Helper\Utility;

/**
 * AMQP Agent base exception class.
 * @since 1.0.0
 */
class AmqpAgentException extends CoreException
{
    /**
     * Redefine the exception so message is not an optional parameter.
     * @param string $message
     * @param int $code
     * @param CoreException|null $previous
     */
    public function __construct(string $message, int $code = 0, CoreException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * String representation of the object.
     * @return string
     */
    public function __toString()
    {
        return static::class . ": [{$this->code}]: {$this->message}\n{$this->getTraceAsString()}\n";
    }

    /**
     * Rethrows an exception with an additional message.
     * @param CoreException $exception The exception to rethrow.
     * @param string|null $message [optional] An additional message to add to the wrapping exception before the message of the passed exception.
     * @param string|bool $wrap [optional] Whether to throw the exception using the passed class (FQN), in the same exception type (true), or wrap it with the class this method was called on (false). Any other value will be translated to false. Defaults to true.
     * @return void
     * @throws CoreException
     */
    public static function rethrow(CoreException $exception, ?string $message = null, $wrap = false): void
    {
        if (null === $message) {
            $trace = Utility::backtrace(['file', 'line', 'class', 'function']);
            $prefix = (isset($trace['class']) ? "{$trace['class']}::" : "{$trace['file']}({$trace['line']}): ");
            $suffix = "{$trace['function']}() failed!";
            $message = 'Rethrown Exception: ' . $prefix . $suffix . ' ';
        } else {
            $message = strlen($message) ? $message . ' ' : $message;
        }

        $error = is_string($wrap)
            ? (
                class_exists($wrap) && is_subclass_of($wrap, 'Exception')
                    ? $wrap
                    : static::class
            )
            : (
                boolval($wrap)
                    ? get_class($exception)
                    : static::class
            );

        throw new $error($message . (string)$exception->getMessage(), (int)$exception->getCode(), $exception);
    }

    /**
     * Rethrows an exception with an additional message.
     * @deprecated 1.2.0 Use `self::rethrow()` instead.
     * @param CoreException $exception The exception to rethrow.
     * @param string|null $message [optional] An additional message to add to the wrapping exception before the message of the passed exception.
     * @param string|bool $wrap [optional] Whether to throw the exception using the passed class (FQN), in the same exception type (true), or wrap it with the class this method was called on (false). Any other value will be translated to false.
     * @return void
     * @throws CoreException
     */
    public static function rethrowException(CoreException $exception, ?string $message = null, $wrap = false): void
    {
        static::rethrow($exception, $message, $wrap);
    }
}
