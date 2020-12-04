<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

use stdClass;
use ReflectionObject;

/**
 * A trait containing methods for for manipulating and working arrays.
 * @since 2.0.0
 */
trait ArrayProxyTrait
{
    /**
     * Gets a value from an array via dot-notation representation.
     * @param array $array The array to get the value from.
     * @param string $key The dotted key representation.
     * @param mixed $default [optional] The default fallback value.
     * @return mixed The requested value if found otherwise the default parameter.
     */
    public static function getArrayValueByKey(array &$array, string $key, $default = null)
    {
        if (!strlen($key) || !count($array)) {
            return $default;
        }

        $data = &$array;

        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);

            foreach ($parts as $part) {
                if (!array_key_exists($part, $data)) {
                    return $default;
                }

                $data = &$data[$part];
            }

            return $data;
        }

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * Sets a value of an array via dot-notation representation.
     * @param array $array The array to set the value in.
     * @param string $key The string key representation.
     * @param mixed $value The value to set.
     * @return bool True on success.
     */
    public static function setArrayValueByKey(array &$array, string $key, $value): bool
    {
        if (!strlen($key)) {
            return false;
        }

        $parts = explode('.', $key);
        $lastPart = array_pop($parts);

        $data = &$array;

        if (!empty($parts)) {
            foreach ($parts as $part) {
                if (!isset($data[$part])) {
                    $data[$part] = [];
                }

                $data = &$data[$part];
            }
        }

        $data[$lastPart] = $value;

        return true;
    }

    /**
     * Returns a string representation of an array by imploding it recursively with common formatting of data-types.
     * @param array $array The array to implode.
     * @return string
     */
    public static function castArrayToString(array $array): string
    {
        $pieces = [];

        foreach ($array as $item) {
            switch (true) {
                case (is_array($item)):
                    $pieces[] = self::castArrayToString($item);
                    break;
                case (is_object($item)):
                    $pieces[] = get_class($item) ?? 'object';
                    break;
                case (is_string($item)):
                    $pieces[] = "'{$item}'";
                    break;
                case (is_bool($item)):
                    $pieces[] = $item ? 'true' : 'false';
                    break;
                case (is_null($item)):
                    $pieces[] = 'null';
                    break;
                default:
                    $pieces[] = $item;
            }
        }

        return '[' . implode(', ', $pieces). ']';
    }

    /**
     * Converts (casts) an array to an object (stdClass).
     * @param array $array The array to convert.
     * @param bool $useJson [optional] Wether to use json_decode/json_encode to cast the array, default is via iteration.
     * @return stdClass The result object.
     */
    public static function castArrayToObject(array $array, bool $useJson = false): stdClass
    {
        if ($useJson) {
            return json_decode(json_encode($array));
        }

        $stdClass = new stdClass();

        foreach ($array as $key => $value) {
            $stdClass->{$key} = is_array($value)
                ? self::castArrayToObject($value, $useJson)
                : $value;
        }

        return $stdClass;
    }

    /**
     * Converts (casts) an object to an associative array.
     * @param object $object The object to convert.
     * @param bool $useJson [optional] Wether to use json_decode/json_encode to cast the object, default is via reflection.
     * @return array The result array.
     */
    public static function castObjectToArray($object, bool $useJson = false): array
    {
        if ($useJson) {
            return json_decode(json_encode($object), true);
        }

        $array = [];

        $reflectionClass = new ReflectionObject($object);
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($object);
            $property->setAccessible(false);
        }

        return $array;
    }
}
