<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Worker;

use Exception;
use ReflectionClass;
use MAKS\AmqpAgent\Helper\Singleton;
use MAKS\AmqpAgent\Worker\AbstractWorker;

/**
 * An abstract class implementing mapping functions to turn a normal worker into a singleton.
 * @since 1.0.0
 */
abstract class AbstractWorkerSingleton extends Singleton
{
    /**
     * The full qualified name of the instanciated class.
     * @var string
     */
    protected static $class;

    /**
     * The instance of the worker class (a class that extends AbstactWorker).
     * Sub-classes of this class should instantiate a worker and set it
     * to the protected $worker property in their __construct() method.
     * @var AbstractWorker
     */
    protected $worker;


    /**
     * Returns an instance of the class this method was called on.
     * @param array ...$arguments The same arguments of the normal worker.
     * @return self
     */
    public static function getInstance()
    {
        $worker = parent::getInstance();
        $workerReference = $worker->worker;

        static::$class = get_class($workerReference);

        $arguments = func_get_args();
        $argsCount = func_num_args();

        if ($argsCount > 0) {
            $reflection = new ReflectionClass($workerReference);
            $properties = $reflection->getConstructor()->getParameters();

            $index = 0;
            foreach ($properties as $property) {
                $member = $property->getName();
                $workerReference->{$member} = $arguments[$index];
                $index++;
                if ($index === $argsCount) {
                    break;
                }
            }
        }

        return $worker;
    }


    /**
     * Gets a class member via public property access notation.
     * @param string $member Property name.
     * @return mixed
     */
    public function __get(string $member)
    {
        if (defined(static::$class . '::' . $member)) {
            return constant(static::$class . '::' . $member);
        } elseif (isset(static::$class::$$member)) {
            return static::$class::$$member;
        }

        return $this->worker->$member;
    }

    /**
     * Sets a class member via public property assignment notation.
     * @param string $member Property name.
     * @param mixed $value Override for object property or a static property.
     * @return void
     */
    public function __set(string $member, $value)
    {
        if (isset(static::$class::$$member)) {
            static::$class::$$member = $value;
            return;
        }

        $this->worker->{$member} = $value;
    }

    /**
     * Calls a method on a class that extend AbstractWorker and throws an exception for calls to undefined methods.
     * @param string $function Function name.
     * @param array $arguments Function arguments.
     * @return mixed
     * @throws MethodDoesNotExistException
     */
    public function __call(string $method, array $arguments)
    {
        $return = $this->worker->{$method}(...$arguments);

        // check to return the right object to allow for triuble-free chaining.
        if ($return instanceof $this->worker) {
            return $this;
        }

        return $return;
    }

    /**
     * Calls a method on a class that extend AbstractWorker and throws an exception for calls to undefined static methods.
     * @param string $function Function name.
     * @param array $arguments Function arguments.
     * @return mixed
     * @throws MethodDoesNotExistException
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $function = static::$class . '::' . $method;
        return forward_static_call_array($function, $arguments);
    }
}
