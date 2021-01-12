<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Worker;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use MAKS\AmqpAgent\Worker\Consumer;
use MAKS\AmqpAgent\Worker\AbstractWorkerSingleton;

/**
 * A singleton version of the Consumer class.
 * Static and constant properties are accessed via object operator (`->` not `::`).
 *
 * Example:
 * ```
 * $consumer = ConsumerSingleton::getInstance();
 * ```
 *
 * @since 1.0.0
 * @api
 * @see \MAKS\AmqpAgent\Worker\Consumer for the full API.
 * @method self connect()
 * @method self disconnect()
 * @method self reconnect()
 * @method self queue(?array $parameters = null, ?AMQPChannel $_channel = null)
 * @method ?AMQPStreamConnection getConnection()
 * @method self setConnection(AMQPStreamConnection $connection)
 * @method ?AMQPChannel getChannel()
 * @method self setChannel(AMQPChannel $channel)
 * @method ?AMQPChannel getNewChannel(array $parameters = null, ?AMQPStreamConnection $_connection = null)
 * @method ?AMQPChannel getChannelById(array $parameters = null)
 * @method self qos(?array $parameters = null, ?AMQPChannel $_channel = null)
 * @method self consume($callback = null, ?array $variables = null, ?array $parameters = null, ?AMQPChannel $_channel = null)
 * @method bool isConsuming(?AMQPChannel $_channel = null)
 * @method self wait(?array $parameters = null, ?AMQPChannel $_channel = null)
 * @method self waitForAll(?array $parameters = null, ?AMQPStreamConnection $_connection = null)
 * @method self prepare()
 * @method void work($callback)
 * @method static AMQPTable arguments(array $array)
 * @method static bool shutdown(...$object)
 * @method static array makeCommand(string $name, string $value, $parameters = null, string $argument = 'params')
 * @method static bool isCommand($data)
 * @method static bool hasCommand(array $data, string $name = null, ?string $value = null)
 * @method static mixed getCommand(array $data, string $key = 'params', ?string $sub = null)
 * @method static void ack(AMQPMessage $_message, ?array $parameters)
 * @method static void nack(?AMQPChannel $_channel = null, AMQPMessage $_message, ?array $parameters = null)
 * @method static ?AMQPMessage get(AMQPChannel $_channel, ?array $parameters = null)
 * @method static mixed cancel(AMQPChannel $_channel, ?array $parameters = null)
 * @method static mixed recover(AMQPChannel $_channel, ?array $parameters = null)
 * @method static void reject(AMQPMessage $_message, ?array $parameters = null)
 */
final class ConsumerSingleton extends AbstractWorkerSingleton
{
    /**
     * Use ConsumerSingleton::getInstance() instead.
     */
    public function __construct()
    {
        $this->worker = new Consumer();
    }
}
