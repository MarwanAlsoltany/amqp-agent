<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

use MAKS\AmqpAgent\Helper\ArrayProxyTrait;

/**
 * A class containing methods for for manipulating and working arrays.
 *
 * Get example:
 * ```
 * ArrayProxy::get($array, 'some.key', 'Default/Fallback Value');
 * ```
 * Set example:
 * ```
 * ArrayProxy::set($array, 'some.key', $newValue);
 * ```
 * Cast (array to string) example:
 * ```
 * ArrayProxy::arrayToString($array);
 * ```
 * Cast (array to object) example:
 * ```
 * ArrayProxy::arrayToObject($array);
 * ```
 * Cast (object to array) example:
 * ```
 * ArrayProxy::objectToArray($object);
 * ```
 *
 * @since 2.0.0
 */
class ArrayProxy
{
    use ArrayProxyTrait {
        getArrayValueByKey as get;
        setArrayValueByKey as set;
        castArrayToString as arrayToString;
        castArrayToObject as arrayToObject;
        castArrayToObject as objectToArray;
    }
}
