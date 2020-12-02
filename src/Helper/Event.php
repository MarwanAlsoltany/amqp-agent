<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

use MAKS\AmqpAgent\Helper\EventTrait;

/**
 * A simple class for handling events (dispatching and listening).
 *
 * Dispatch example:
 * ```
 * Event::dispatch('some.event.fired', [$arg1, $arg2]);
 * ```
 * Listen example:
 * ```
 * Event::listen('some.event.fired', function ($arg1, $arg2) {
 *     mail('name@domain.tld', "The {$arg1} is ...!", "{$arg2} has been ....");
 * });
 * ```
 *
 * @since 2.0.0
 */
class Event
{
    use EventTrait {
        bind as public listen;
        trigger as public dispatch;
    }
}
