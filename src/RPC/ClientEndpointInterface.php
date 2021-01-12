<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\RPC;

use PhpAmqpLib\Message\AMQPMessage;
use MAKS\AmqpAgent\RPC\AbstractEndpointInterface;

/**
 * An interface defining the basic methods of a client.
 * @since 2.0.0
 */
interface ClientEndpointInterface extends AbstractEndpointInterface
{
    /**
     * Sends the passed request to the server using the passed queue.
     * @param string|AMQPMessage $request The request body or an `AMQPMessage` instance.
     * @param string|null $queueName [optional] The name of queue to send through.
     * @return string The response body.
     */
    public function request($request, ?string $queueName = null): string;

    /**
     * Sends the passed request to the server using the passed queue.
     * Alias for `self::request()`.
     * @param string|AMQPMessage $request The request body or an `AMQPMessage` instance.
     * @param string|null $queueName [optional] The name of queue to send through.
     * @return string The response body.
     */
    public function call($request, ?string $queueName = null): string;
}
