<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

use MAKS\AmqpAgent\Helper\ClassProxyTrait;

/**
 * A class containing methods for proxy methods calling, properties manipulation, and class utilities.
 *
 * Call example:
 * ```
 * ClassProxy::call($object, 'someMethod', $arguments);
 * ```
 * Get example:
 * ```
 * ClassProxy::get($object, 'someProperty');
 * ```
 * Set example:
 * ```
 * ClassProxy::set($object, 'someProperty', $newValue);
 * ```
 * Cast example:
 * ```
 * ClassProxy::cast($object, 'Namespace\SomeClass');
 * ```
 *
 * @since 2.0.0
 */
class ClassProxy
{
    use ClassProxyTrait {
        callMethod as call;
        setProperty as set;
        getProperty as get;
        castObjectToClass as cast;
    }
}
