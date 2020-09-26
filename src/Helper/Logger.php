<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
            Utility::emit(
                [
                    'yellow' => sprintf('%s() was called without specifying a filename.', __METHOD__),
                    'green'  => sprintf('Log file will be named: "%s".', $filename)
                ],
                null,
                1024
            );
        }

        if (null === $directory) {
            $backtrace = Utility::backtrace(['file']);
            $fallback1 = strlen($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["DOCUMENT_ROOT"] : null;
            $fallback2 = isset($backtrace['file']) ? dirname($backtrace['file']) : __DIR__;
            $directory = $fallback1 ?? $fallback2;
            Utility::emit(
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
                    $signature = fgets($stream) . 'For exceeding 64MB, it was overwritten on ' . date('l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL;
                    fclose($stream);
                    file_put_contents($file, $signature, null, stream_context_create());
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
}
