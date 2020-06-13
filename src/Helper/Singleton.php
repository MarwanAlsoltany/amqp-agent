<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

use MAKS\AmqpAgent\Exception\SingletonViolationException;

/**
 * An abstract class implementing the fundamental functionality of a singleton.
 * @since 1.0.0
 */
abstract class Singleton
{
    /**
     * Each sub-class of the Singleton stores its own instance here.
     * @var array
     */
    private static $instances = [];


    /**
     * Can't be private if we want to allow subclassing.
     */
    protected function __construct()
    {
        //
    }

    /**
     * Cloning is not permitted for singletons.
     */
    public function __clone()
    {
        throw new SingletonViolationException("Bad call. Cannot clone a singleton!");
    }

    /**
     * Serialization is not permitted for singletons.
     */
    public function __sleep()
    {
        throw new SingletonViolationException("Bad call. Cannot serialize a singleton!");
    }

    /**
     * Unserialization is not permitted for singletons.
     */
    public function __wakeup()
    {
        throw new SingletonViolationException("Bad call. Cannot unserialize a singleton!");
    }


    /**
     * The method used to get the singleton's instance.
     * @return self
     */
    public static function getInstance()
    {
        $subclass = static::class;
        if (!isset(self::$instances[$subclass])) {
            self::$instances[$subclass] = new static;
        }
        return self::$instances[$subclass];
    }


    /**
     * Destroys the singleton's instance it was called on.
     * @param self $object The instance it was called on.
     * @return void
     */
    public function destroyInstance(&$object)
    {
        if (is_subclass_of($object, __CLASS__)) {
            $object = null;
        }
    }
}
