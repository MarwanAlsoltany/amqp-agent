<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Helper;

use MAKS\AmqpAgent\Helper\Utility;

/**
 * A class to write logs, exposing methods that work statically and on instantiation.
 * This class DOES NOT implement `Psr\Log\LoggerInterface`.
 *
 * Example:
 * ```
 * // static
 * Logger::log('Some message to log.', 'filename', 'path/to/some/directory');
 * // instantiated
 * $logger = new Logger();
 * $logger->setFilename('filename');
 * $logger->setDirectory('path/to/some/directory');
 * $logger->write('Some message to log.');
 * ```
 *
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
     * @param string|null $filename The name wished to be given to the file. Pass null for auto-generate.
     * @param string|null $directory The directory where the log file should be written.
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
     * @param string $filename The filename.
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
     * @param string $directory The directory.
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
     * @param string|null $filename [optional] The name wished to be given to the file. If not provided a Notice will be raised with the auto-generated filename.
     * @param string|null $directory [optional] The directory where the log file should be written. If not provided a Warning will be raised with the used path.
     * @return bool True if message was written.
     */
    public static function log(string $message, ?string $filename = null, ?string $directory = null): bool
    {
        $passed = false;

        if (null === $filename) {
            $filename = self::getFallbackFilename();
            Utility::emit(
                [
                    'yellow' => sprintf('%s() was called without specifying a filename.', __METHOD__),
                    'green'  => sprintf('Log file will be named: "%s".', $filename)
                ],
                null,
                E_USER_NOTICE
            );
        }

        if (null === $directory) {
            $directory = self::getFallbackDirectory();
            Utility::emit(
                [
                    'yellow' => sprintf('%s() was called without specifying a directory.', __METHOD__),
                    'red'    => sprintf('Log file will be written in: "%s".', $directory)
                ],
                null,
                E_USER_WARNING
            );
        }

        $file = self::getNormalizedPath($directory, $filename);

        // create log file if it does not exist
        if (!is_file($file) && is_writable($directory)) {
            $signature = 'Created by ' . __METHOD__ . date('() \o\\n l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL;
            file_put_contents($file, $signature, 0, stream_context_create());
            chmod($file, 0775);
        }

        // write in the log file
        if (is_writable($file)) {
            // empty the the file if it exceeds 64MB
            // @codeCoverageIgnoreStart
            clearstatcache(true, $file);
            if (filesize($file) > 6.4e+7) {
                $stream = fopen($file, 'r');
                if (is_resource($stream)) {
                    $signature = fgets($stream) . 'For exceeding 64MB, it was overwritten on ' . date('l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL;
                    fclose($stream);
                    file_put_contents($file, $signature, 0, stream_context_create());
                    chmod($file, 0775);
                }
            }
            // @codeCoverageIgnoreEnd

            $timestamp = Utility::time()->format(DATE_ISO8601);
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
     * Returns a fallback filename based on date.
     * @since 1.2.1
     * @return string
     */
    protected static function getFallbackFilename(): string
    {
        return 'maks-amqp-agent-log-' . date("Ymd");
    }

    /**
     * Returns a fallback writing directory based on caller.
     * @since 1.2.1
     * @return string
     */
    protected static function getFallbackDirectory(): string
    {
        $backtrace = Utility::backtrace(['file'], 0);
        $fallback1 = strlen($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["DOCUMENT_ROOT"] : null;
        $fallback2 = isset($backtrace['file']) ? dirname($backtrace['file']) : __DIR__;

        return $fallback1 ?? $fallback2;
    }

    /**
     * Returns a normalized path based on OS.
     * @since 1.2.1
     * @param string $directory The directory.
     * @param string $filename The Filename.
     * @return string The full normalized path.
     */
    protected static function getNormalizedPath(string $directory, string $filename): string
    {
        $ext = '.log';
        $filename = substr($filename, -strlen($ext)) === $ext ? $filename : $filename . $ext;
        $directory = $directory . DIRECTORY_SEPARATOR;
        $path = $directory . $filename;

        return preg_replace("/\/+|\\+/", DIRECTORY_SEPARATOR, $path);
    }
}
