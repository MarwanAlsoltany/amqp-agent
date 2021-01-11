<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Config;

use MAKS\AmqpAgent\Exception\ConstantDoesNotExistException;

/**
 * An abstract class that exposes a simple API to work with parameters.
 * @since 1.2.0
 */
abstract class AbstractParameters
{
    /**
     * Patches the passed array with a class constant.
     * @param array $options The partial array.
     * @param string $const The constant name.
     * @param bool $values Wether to return values only or an associative array.
     * @return array The final patched array.
     */
    final public static function patch(array $options, string $const, bool $values = false): array
    {
        $final = null;
        $const = static::class . '::' . $const;

        if (defined($const)) {
            $const = constant($const);

            $final = is_array($const) ? self::patchWith($options, $const, $values) : $final;
        }

        if (null !== $final) {
            return $final;
        }

        throw new ConstantDoesNotExistException(
            sprintf(
                'Could not find a constant with the name "%s", or the constant is not of type array!',
                $const
            )
        );
    }

    /**
     * Patches the passed array with another array.
     * @param array $partialArray The partial array.
     * @param array $fullArray The full array.
     * @param bool $values Wether to return values only or an associative array.
     * @return array The final patched array.
     */
    final public static function patchWith(array $partialArray, array $fullArray, bool $values = false): array
    {
        $final = (
            array_merge(
                $fullArray,
                array_intersect_key(
                    $partialArray,
                    $fullArray
                )
            )
        );

        return !$values ? $final : array_values($final);
    }
}
