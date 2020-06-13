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
 * A class to write logs, exposing methods that work statically and on instantiation.
 * @since 1.0.0
 * @method bool log(string $message, string $filename, string $directory)
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
            $filename = 'maks-amqp-agent-log-' . date("d-m-Y");
            static::emit(
                [__METHOD__.'() was called without specifying a filename.', 'style' => 'Log file will be named: ' . $filename . '.'],
                'yellow',
                1024
            );
        }

        if (null === $directory) {
            [0 => ['file' => $fallback]] = array_reverse(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            $directory = strlen($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["DOCUMENT_ROOT"] : dirname($fallback);
            static::emit(
                [__METHOD__.'() was called without specifying a directory.', 'style' => 'Log file will be written in: ' . $directory . '.'],
                'red',
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
                fwrite($stream, $log . PHP_EOL);
                fclose($stream);
                $passed = true;
            }
        }

        return $passed;
    }

    /**
     * Generates a user-level notice, warning or error with styling.
     * @param array|string|null $text The text wished to be styled (when passing an array styled elements must have string keys).
     * @param string $color (red, green, yellow, magenta, cyan, gray) or their initial letter other values translate to white.
     * @param int $type Error type (E_USER family). 1024 E_USER_NOTICE, 512 E_USER_WARNING, 256 E_USER_ERROR, 16384 E_USER_DEPRECATED.
     * @return bool True if error type is accepted.
     * @codeCoverageIgnore
     */
    protected static function emit($text = null, ?string $color = 'yellow', int $type = E_USER_NOTICE): bool
    {
        switch ($color) {
            case 'r':
            case 'red':
                $color = '[31m';
                break;
            case 'g':
            case 'green':
                $color = '[32m';
                break;
            case 'y':
            case 'yellow':
                $color = '[33m';
                break;
            case 'b':
            case 'blue':
                $color = '[34m';
                break;
            case 'm':
            case 'magenta':
                $color = '[35m';
                break;
            case 'c':
            case 'cyan':
                $color = '[36m';
                break;
            case 'g':
            case 'gray':
                $color = '[37m';
                break;
            default:
                $color = '[39m';
        }

        $trim = ' \t\0\x0B';
        $backspace = chr(8);
        $wrapper = "\033" . $color . ' %s' . "\033[39m";
        $message = '';

        if (is_array($text)) {
            foreach ($text as $name => $value) {
                $string = trim($value, $trim);
                if (is_string($name)) {
                    $message .= !strlen($message)
                        ? sprintf($wrapper, $backspace . $string)
                        : sprintf($wrapper, $string);
                    continue;
                }
                $message = $message . $string;
            }
        } elseif (is_string($text)) {
            $string = $backspace . trim($text, $trim);
            $message = sprintf($wrapper, $string);
        } else {
            $string = $backspace . 'From ' . __CLASS__ . ': No message was specified!';
            $message = sprintf($wrapper, $string);
        }

        return trigger_error($message, $type);
    }
}
