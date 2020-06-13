<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Helper;

use Exception;
use MAKS\AmqpAgent\Helper\Logger;
use MAKS\AmqpAgent\Helper\Serializer;
use MAKS\AmqpAgent\Worker\Consumer;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * An abstarct class used as a default callback for the consumer.
 * @since 1.0.0
 */
abstract class Example
{
    /**
     * @var Serializer
     */
    private static $serializer;

    /**
     * Wether to log messages to a file or not.
     * @var bool
     */
    public static $logToFile = true;


    /**
     * Default AMQP Agent callback.
     * @param AMQPMessage $message
     * @return bool
     */
    public static function callback(AMQPMessage $message): bool
    {
        if (!isset(self::$serializer)) {
            self::$serializer = new Serializer;
        }

        try {
            $data = self::$serializer->unserialize($message->body, 'PHP');
        } catch (Exception $e) {
            $data = self::$serializer->unserialize($message->body, 'JSON');
        } catch (Exception $e) { // @codeCoverageIgnore
            // Ignore error silently.
        } // @codeCoverageIgnore

        Consumer::ack($message);

        if ($data) {
            if (Consumer::isCommand($data)) {
                usleep(25000); // For acknowledgment to take effect.
                if (Consumer::hasCommand($data, 'close')) {
                    Consumer::shutdown($message);
                }
            }
        }

        if (static::$logToFile) {
            Logger::log($message->body, 'maks-amqp-agent-example-callback'); // @codeCoverageIgnore
        }

        return true;
    }
}
