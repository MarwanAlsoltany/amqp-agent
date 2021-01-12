<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\RPC;

use MAKS\AmqpAgent\RPC\AbstractEndpointInterface;

/**
 * An interface defining the basic methods of a server.
 * @since 2.0.0
 */
interface ServerEndpointInterface extends AbstractEndpointInterface
{
    /**
     * Listens on requests coming via the passed queue and processes them with the passed callback.
     * Alias for `self::respond()`.
     * @param callable|null $callback [optional] The callback to process the request. This callback will be passed an `AMQPMessage` and must return a string.
     * @param string|null $queueName [optional] The name of the queue to listen on.
     * @return string The last processed request.
     */
    public function respond(?callable $callback = null, ?string $queueName = null): string;

    /**
     * Listens on requests coming via the passed queue and processes them with the passed callback.
     * Alias for `self::respond()`.
     * @param callable|null $callback [optional] The callback to process the request. This callback will be passed an `AMQPMessage` and must return a string.
     * @param string|null $queueName [optional] The name of the queue to listen on.
     * @return string The last processed request.
     */
    public function serve(?callable $callback = null, ?string $queueName = null): string;
}
