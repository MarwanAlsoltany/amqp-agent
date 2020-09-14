<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

use DateTime;

/**
 * A class to write logs, exposing methods that work statically and on instantiation. This class DOES NOT implement \Psr\Log\LoggerInterface.
 * @since 1.0.0
 */
class Logger
{
    /**
     * The filename of the log file.
     * @var string
     */
    public $filename;

    /**
     * The directory where the log file gets written.
     * @var string
     */
    public $directory;


    /**
     * Passing null for $directory will raise a warning and force the logger to find a reasonable directory to write the file in.
     * @param string $filename The name wished to be given to the file. Pass null for auto-generate.
     * @param string $directory The directory where the log file should be written.
     */
    public function __construct(?string $filename, ?string $directory)
    {
        $this->filename = $filename;
        $this->directory = $directory;
    }


    /**
     * Logs a message to a file, generates it if it does not exist and raises a user-level warning and/or notice on misuse.
     * @param string $message The message wished to be logged.
     * @return bool True on success.
     */
    public function write(string $message): bool
    {
        return self::log($message, $this->filename, $this->directory);
    }

    /**
     * Gets filename property.
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Sets filename property.
     * @param string $filename  Filename
     * @return self
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Gets directory property.
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Sets directory property.
     * @param string $directory  Directory
     * @return self
     */
    public function setDirectory(string $directory)
    {
        $this->directory = $directory;
        return $this;
    }


    /**
     * Logs a message to a file, generates it if it does not exist and raises a user-level warning and/or notice on misuse.
     * @param string $message The message wished to be logged.
     * @param string $filename [optional] The name wished to be given to the file.
     * @param string $directory [optional] The directory where the log file should be written.
     * @return bool True if message was written.
     */
    public static function log(string $message, ?string $filename = null, ?string $directory = null): bool
    {
        $passed = false;

        if (null === $filename) {
            $filename = 'maks-amqp-agent-log-' . date("Ymd");
            static::emit(
                [
                    'yellow' => sprintf('%s() was called without specifying a filename.', __METHOD__),
                    'green'  => sprintf('Log file will be named: "%s".', $filename)
                ],
                null,
                1024
            );
        }

        if (null === $directory) {
            [0 => ['file' => $fallback]] = array_reverse(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            $directory = strlen($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["DOCUMENT_ROOT"] : dirname($fallback);
            static::emit(
                [
                    'yellow' => sprintf('%s() was called without specifying a directory.', __METHOD__),
                    'red'    => sprintf('Log file will be written in: "%s".', $directory)
                ],
                null,
                512
            );
        }

        $file = $directory . DIRECTORY_SEPARATOR . $filename . '.log';
        $file = preg_replace("/\/+|\\+/", DIRECTORY_SEPARATOR, $file);

        // create log file if it does not exist
        if (!is_file($file) && is_writable($directory)) {
            $signature = 'Created by ' . __METHOD__ . date('() \o\\n l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL;
            file_put_contents($file, $signature, null, stream_context_create());
            chmod($file, 0775);
        }

        // write in the log file
        if (is_writable($file)) {
            // empty the the file if it exceeds 64MB
            // @codeCoverageIgnoreStart
            if (filesize($file) > 6.4e+7) {
                $stream = fopen($file, 'r');
                if (is_resource($stream)) {
                    $signature = fgets($stream) . 'For exceeding 64MB, it was overwitten on ' . date('l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL;
                    fclose($stream);
                    file_put_contents($file, $signature, null, stream_context_create());
                    chmod($file, 0775);
                }
            }
            // @codeCoverageIgnoreEnd

            $date = new DateTime('now');
            $timestamp = $date->format(DateTime::ISO8601);
            $log = $timestamp . ' ' . $message . PHP_EOL;

            $stream = fopen($file, 'a+');
            if (is_resource($stream)) {
                fwrite($stream, $log);
                fclose($stream);
                $passed = true;
            }
        }

        return $passed;
    }

    /**
     * Generates a user-level notice, warning, or an error with styling.
     * @param array|string|null [optional] $text The text wished to be styled (when passing an array, if array key is a valid color it will style this array element value with its key).
     * @param string [optional] $color Case sensitive color name in this list [red, green, yellow, magenta, cyan, gray] (when passing array, this parameter will be the fallback).
     * @param int [optional] $type Error type (E_USER family). 1024 E_USER_NOTICE, 512 E_USER_WARNING, 256 E_USER_ERROR, 16384 E_USER_DEPRECATED.
     * @return bool True if error type is accepted.
     * @codeCoverageIgnore
     */
    protected static function emit($text = null, ?string $color = 'yellow', int $type = E_USER_NOTICE): bool
    {
        $colors = [
            'red'     => 31,
            'green'   => 32,
            'yellow'  => 33,
            'blue'    => 34,
            'magenta' => 35,
            'cyan'    => 36,
            'gray'    => 37,
            'default' => 39,
        ];

        $types = [
            E_USER_NOTICE     => E_USER_NOTICE,
            E_USER_WARNING    => E_USER_WARNING,
            E_USER_ERROR      => E_USER_ERROR,
            E_USER_DEPRECATED => E_USER_DEPRECATED,
        ];

        $cli = php_sapi_name() == 'cli';
        $trim = ' \t\0\x0B';
        $backspace = chr(8);
        $wrapper = $cli ? "\033[%dm %s\033[39m" : "@CLR[%d] %s";
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
            $string = $backspace . 'From ' . __CLASS__ . ': No message was specified!';
            $message = sprintf($wrapper, $color, $string);
        }

        $message = $cli ? $message : preg_replace('/@CLR\[\d+\]/', '', $message);

        return trigger_error($message, $type);
    }
}
