<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

use Closure;

/**
 * A trait containing events handling functions (adds events triggering and binding capabilities) to a class.
 * @since 2.0.0
 */
trait EventTrait
{
    /**
     * Here lives all bindings.
     * @var array
     */
    protected static $events = [];

    /**
     * Executes callbacks attached to the passed event with the passed arguments.
     * @param string $event Event name.
     * @param array $arguments [optional] Arguments array. Note that the arguments will be spread (`...$args`) on the callback.
     * @return void
     */
    protected static function trigger(string $event, array $arguments = []): void
    {
        if (isset(self::$events[$event]) && count(self::$events[$event])) {
            $callbacks = &self::$events[$event];
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, array_values($arguments));
            }
        } else {
            self::$events[$event] = [];
        }
    }

    /**
     * Binds the passed function to the passed event.
     * @param string $event Event name.
     * @param Closure $function A closure to process the event.
     * @return void
     */
    protected static function bind(string $event, Closure $function): void
    {
        self::$events[$event][] = $function;
    }

    /**
     * Returns array of all registered events as an array `['event.name' => [$cb1, $cb2, ...]]`.
     * @return array
     */
    public static function getEvents(): array
    {
        return self::$events;
    }
}
