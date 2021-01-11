<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Helper;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use MAKS\AmqpAgent\Helper\Logger;
use MAKS\AmqpAgent\Helper\Serializer;
use MAKS\AmqpAgent\Worker\Consumer;

/**
 * An abstract class used as a default callback for the consumer.
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
            self::$serializer = new Serializer();
        }

        try {
            $data = self::$serializer->unserialize($message->body, 'PHP', true);
        } catch (Exception $e) {
            // the strict value of the serializer is false here
            // because the data can also be plain-text
            $data = self::$serializer->unserialize($message->body, 'JSON', false);
        }

        Consumer::ack($message);

        if ($data && Consumer::isCommand($data)) {
            usleep(25000); // For acknowledgment to take effect.
            if (Consumer::hasCommand($data, 'close')) {
                Consumer::shutdown($message);
            }
        }

        if (static::$logToFile) {
            Logger::log($message->body, 'maks-amqp-agent-example-callback'); // @codeCoverageIgnore
        }

        return true;
    }
}
