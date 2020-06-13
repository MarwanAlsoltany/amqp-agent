<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Exception;

use Exception as CoreException;

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
     * @param CoreException $previous
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
        return __CLASS__ . ": [{$this->code}]: {$this->message} \n";
    }

    /**
     * Rethrows an exception with additional message.
     * @param CoreException $exception
     * @param string $message
     * @return void
     */
    public static function rethrowException(CoreException $exception, string $message = 'Rethrown Exception!'): void
    {
        $error = get_class($exception);
        throw new $error($message, (int)$exception->getCode(), $exception);
    }
}
