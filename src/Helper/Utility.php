<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Helper;

use stdClass;
use Exception;
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
     * Executes a CLI command in the specified path synchronously or asynchronous (cross platform).
     * @since 2.0.0
     * @param string $command The command to execute.
     * @param string $path [optional] The path where the command should be executed.
     * @param bool $asynchronous [optional] Wether the command should be a background process (asynchronous) or not (synchronous).
     * @return string|null The command result (as a string if possible) if synchronous otherwise null.
     */
    public static function execute(string $command, string $path = null, bool $asynchronous = false): ?string
    {
        if (!strlen($command)) {
            throw new Exception('No valid command is specified!');
        }

        $isWindows = PHP_OS == 'WINNT' || substr(php_uname(), 0, 7) == 'Windows';
        $apWrapper = $isWindows ? 'start /B %s > NUL' : '/usr/bin/nohup %s >/dev/null 2>&1 &';

        if (strlen($path) && getcwd() !== $path) {
            chdir(realpath($path));
        }

        if ($asynchronous) {
            $command = sprintf($apWrapper, $command);
        }

        if ($isWindows && $asynchronous) {
            pclose(popen($command, 'r'));
            return null;
        }

        return shell_exec($command);
    }
}
