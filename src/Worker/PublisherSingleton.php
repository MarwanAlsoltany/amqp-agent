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
use MAKS\AmqpAgent\Worker\Publisher;
use MAKS\AmqpAgent\Worker\AbstractWorkerSingleton;

/**
 * A singleton version of the Publisher class.
 * Static and constant properties are accessed via object operator (`->` not `::`).
 *
 * Example:
 * ```
 * $publisher = PublisherSingleton::getInstance();
 * ```
 *
 * @since 1.0.0
 * @api
 * @see \MAKS\AmqpAgent\Worker\Publisher for the full API.
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
 * @method self exchange(?array $parameters = null, ?AMQPChannel $_channel = null)
 * @method self bind(?array $parameters = null, ?AMQPChannel $_channel = null)
 * @method AMQPMessage message(string $body, ?array $properties = null)
 * @method self publish($payload, ?array $parameters = null, ?AMQPChannel $_channel = null)
 * @method self publishBatch(array $messages, int $batchSize = 2500, ?array $parameters = null, ?AMQPChannel $_channel = null)
 * @method self prepare()
 * @method void work($messages)
 * @method static AMQPTable arguments(array $array)
 * @method static bool shutdown(...$object)
 * @method static array makeCommand(string $name, string $value, $parameters = null, string $argument = 'params')
 * @method static bool isCommand($data)
 * @method static bool hasCommand(array $data, string $name = null, ?string $value = null)
 * @method static mixed getCommand(array $data, string $key = 'params', ?string $sub = null)
 */
final class PublisherSingleton extends AbstractWorkerSingleton
{
    /**
     * Use PublisherSingleton::getInstance() instead.
     */
    public function __construct()
    {
        $this->worker = new Publisher();
    }
}
