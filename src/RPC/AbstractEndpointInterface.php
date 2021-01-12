<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\RPC;

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * An interface defining the basic methods of an endpoint.
 * @since 2.0.0
 */
interface AbstractEndpointInterface
{
    /**
     * Opens a connection with RabbitMQ server.
     * @param array|null $connectionOptions [optional] The overrides for the default connection options of the RPC endpoint.
     * @return self
     */
    public function connect(?array $connectionOptions = []);

    /**
     * Closes the connection with RabbitMQ server.
     * @return void
     */
    public function disconnect(): void;

    /**
     * Returns whether the endpoint is connected or not.
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Returns the connection used by the endpoint.
     * @return AMQPStreamConnection
     */
    public function getConnection(): AMQPStreamConnection;
}
