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
use DateTime;
use DateTimeZone;

/**
 * A class containing miscellaneous helper functions.
 * @since 1.2.0
 */
final class Utility
{
    /**
     * Returns a DateTime object with the right time zone.
     * @param string $time A valid php date/time string.
     * @param string $timezone A valid php timezone string.
     * @return DateTime
     */
    public static function time(string $time = 'now', ?string $timezone = null): DateTime
    {
        $timezone = $timezone
            ? $timezone
            : date_default_timezone_get();

        $timezoneObject = $timezone
            ? new DateTimeZone($timezone)
            : null;

        return new DateTime($time, $timezoneObject);
    }

    /**
     * Generates a user-level notice, warning, or an error with styling.
     * @param array|string|null $text [optional] The text wished to be styled (when passing an array, if array key is a valid color it will style this array element value with its key).
     * @param string $color [optional] Case sensitive ANSI color name in this list [black, red, green, yellow, magenta, cyan, white, default] (when passing array, this parameter will be the fallback).
     * @param int $type [optional] Error type (E_USER family). 1024 E_USER_NOTICE, 512 E_USER_WARNING, 256 E_USER_ERROR, 16384 E_USER_DEPRECATED.
     * @return bool True if error type is accepted.
     */
    public static function emit($text = null, ?string $color = 'yellow', int $type = E_USER_NOTICE): bool
    {
        $colors = [
            'reset'   => 0,
            'black'   => 30,
            'red'     => 31,
            'green'   => 32,
            'yellow'  => 33,
            'blue'    => 34,
            'magenta' => 35,
            'cyan'    => 36,
            'white'   => 37,
            'default' => 39,
        ];

        $types = [
            E_USER_NOTICE     => E_USER_NOTICE,
            E_USER_WARNING    => E_USER_WARNING,
            E_USER_ERROR      => E_USER_ERROR,
            E_USER_DEPRECATED => E_USER_DEPRECATED,
        ];

        $cli = php_sapi_name() === 'cli' || php_sapi_name() === 'cli-server' || http_response_code() === false;

        $trim = ' \t\0\x0B';
        $backspace = chr(8);
        $wrapper = $cli ? "\033[%dm %s\033[0m" : "@COLOR[%d] %s";
        $color = $colors[$color] ?? 39;
        $type = $types[$type] ?? 1024;
        $message = '';

        if (is_array($text)) {
            foreach ($text as $segmentColor => $string) {
                $string = trim($string, $trim);
                if (is_string($segmentColor)) {
                    $segmentColor = $colors[$segmentColor] ?? $color;
                    $message .= !strlen($message)
                        ? sprintf($wrapper, $segmentColor, $backspace . $string)
                        : sprintf($wrapper, $segmentColor, $string);
                    continue;
                }
                $message = $message . $string;
            }
        } elseif (is_string($text)) {
            $string = $backspace . trim($text, $trim);
            $message = sprintf($wrapper, $color, $string);
        } else {
            $string = $backspace . 'From ' . __METHOD__ . ': No message was specified!';
            $message = sprintf($wrapper, $color, $string);
        }

        $message = $cli ? $message : preg_replace('/@COLOR\[\d+\]/', '', $message);

        return trigger_error($message, $type);
    }

    /**
     * Returns the passed key(s) from the backtrace. Note that the backtrace is reversed (last is first).
     * @param string|array $pluck The key to to get as a string or an array of strings (keys) from this list [file, line, function, class, type, args].
     * @param int $offset [optional] The offset of the backtrace (last executed is index at 0).
     * @return string|int|array|null A string or int if a string is passed, an array if an array is passed and null if no match was found.
     */
    public static function backtrace($pluck, int $offset = 0)
    {
        $backtrace = array_reverse(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT));
        $plucked = null;

        if (count($backtrace) < $offset + 1) {
            return null;
        } elseif (is_string($pluck)) {
            $plucked = isset($backtrace[$offset][$pluck]) ? $backtrace[$offset][$pluck] : null;
        } elseif (is_array($pluck)) {
            $plucked = [];
            foreach ($pluck as $key) {
                !isset($backtrace[$offset][$key]) ?: $plucked[$key] = $backtrace[$offset][$key];
            }
        }

        return is_string($plucked) || is_array($plucked) && count($plucked, COUNT_RECURSIVE) ? $plucked : null;
    }

    /**
     * Returns a string representation of an array by imploding it recursively with common formatting of data-types.
     * @since 1.2.1
     * @param array $pieces The array to implode.
     * @return string
     */
    public static function collapse(array $pieces): string
    {
        $flat = [];

        foreach ($pieces as $piece) {
            if (is_array($piece)) {
                $flat[] = self::collapse($piece);
            } elseif (is_object($piece)) {
                $flat[] = get_class($piece) ?? 'object';
            } elseif (is_string($piece)) {
                $flat[] = "'{$piece}'";
            } elseif (is_bool($piece)) {
                $flat[] = $piece ? 'true' : 'false';
            } elseif (is_null($piece)) {
                $flat[] = 'null';
            } else {
                $flat[] = $piece;
            }
        }

        return '[' . implode(', ', $flat). ']';
    }

    /**
     * Converts (casts) an object to an associative array.
     * @since 1.2.2
     * @param object $object The object to convert.
     * @param bool $useJson [optional] Wether to use json_decode/json_encode to cast the object, default is via reflection.
     * @return array The result array.
     */
    public static function objectToArray($object, bool $useJson = false): array
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

    /**
     * Converts (casts) an array to an object (stdClass).
     * @since 1.2.2
     * @param object $object The array to convert.
     * @param bool $useJson [optional] Wether to use json_decode/json_encode to cast the array, default is via iteration.
     * @return stdClass The result object.
     */
    public static function arrayToObject(array $array, bool $useJson = false): stdClass
    {
        if ($useJson) {
            return json_decode(json_encode($array));
        }

        $stdClass = new stdClass();

        foreach ($array as $key => $value) {
            $stdClass->{$key} = is_array($value)
                ? self::arrayToObject($value, $useJson)
                : $value;
        }

        return $stdClass;
    }

    /**
     * Gets a value from an array via dot-notation representation.
     * @since 1.2.2
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
     * @since 1.2.2
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
     * Generates an md5 hash from microtime and uniqid.
     * @param string $entropy [optional] Additional entropy.
     * @since 2.0.0
     * @return string
     */
    public static function generateHash(string $entropy = 'maks-amqp-agent-id'): string
    {
        $prefix = sprintf('-%s-[%d]-', $entropy, rand());
        $symbol = microtime(true) . uniqid($prefix, true);

        return md5($symbol);
    }

    /**
     * Generates a crypto safe unique token. Note that this function is pretty expensive.
     * @since 2.0.0
     * @param int $length The length of the token. If the token is hashed this will not be the length of the returned string.
     * @param string $charset [optional] A string of characters to generate the token from. Defaults to alphanumeric.
     * @param string $hashing [optional] A name of hashing algorithm to hash the generated token with. Defaults to no hashing.
     * @return string
     */
    public static function generateToken(int $length = 32, ?string $charset = null, ?string $hashing = null): string
    {
        $token = '';
        $charset = $charset ?? (
            implode(range('A', 'Z')) .
            implode(range('a', 'z')) .
            implode(range(0, 9))
        );
        $max = strlen($charset);

        for ($i = 0; $i < $length; $i++) {
            $token .= $charset[
                self::generateCryptoSecureRandom(0, $max - 1)
            ];
        }

        return $hashing ? hash($hashing, $token) : $token;
    }

    /**
     * Generates a crypto secure random number.
     * @since 2.0.0
     * @param int $min
     * @param int $max
     * @return int
     */
    protected static function generateCryptoSecureRandom(int $min, int $max): int
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        }

        $log = ceil(log($range, 2));
        $bytes = (int)(($log / 8) + 1); // length in bytes
        $bits = (int)($log + 1); // length in bits
        $filter = (int)((1 << $bits) - 1); // set all lower bits to 1

        do {
            $random = PHP_VERSION >= 7
                ? random_bytes($bytes)
                : openssl_random_pseudo_bytes($bytes);
            $random = hexdec(bin2hex($random));
            $random = $random & $filter; // discard irrelevant bits
        } while ($random > $range);

        return $min + $random;
    }
}
