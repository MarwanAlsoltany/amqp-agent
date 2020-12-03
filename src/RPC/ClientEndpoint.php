<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\RPC;

use PhpAmqpLib\Message\AMQPMessage;
use MAKS\AmqpAgent\Helper\Utility;
use MAKS\AmqpAgent\Helper\ClassProxy;
use MAKS\AmqpAgent\RPC\AbstractEndpoint;
use MAKS\AmqpAgent\RPC\ClientEndpointInterface;
use MAKS\AmqpAgent\Exception\RPCEndpointException;

/**
 * A class specialized in requesting. Implementing only the methods needed for a client.
 *
 * Example:
 * ```
 * $clientEndpoint = new ClientEndpoint();
 * $clientEndpoint->on('some.event', function () { ... });
 * $clientEndpoint->connect();
 * $clientEndpoint->request('Message Body', 'queue.name');
 * $clientEndpoint->disconnect();
 * ```
 *
 * @since 2.0.0
 * @api
 */
class ClientEndpoint extends AbstractEndpoint implements ClientEndpointInterface
{
    /**
     * Opens a connection with RabbitMQ server.
     * @param array|null $connectionOptions
     * @return self
     */
    public function connect(?array $connectionOptions = [])
    {
        parent::connect($connectionOptions);

        if ($this->isConnected()) {
            list($this->responseQueue, , ) = $this->channel->queue_declare(
                null,
                false,
                false,
                true,
                false
            );

            $this->channel->basic_consume(
                $this->responseQueue,
                null,
                false,
                false,
                false,
                false,
                function ($message) {
                    ClassProxy::call($this, 'onResponse', $message);
                }
            );
        }

        return $this;
    }

    /**
     * Sends the passed request to the server using the passed queue.
     * @param string|AMQPMessage $request The request body or an `AMQPMessage` instance.
     * @param string|null $queueName [optional] The name of queue to send through.
     * @return string The response body.
     * @throws RPCEndpointException If the client is not connected yet or if request Correlation ID does not match the one of the response.
     */
    public function request($request, ?string $queueName = null): string
    {
        if (!$this->isConnected()) {
            throw new RPCEndpointException('Client is not connected yet!');
        }

        $this->queueName = $queueName ?? $this->queueName;
        $this->requestBody = $request instanceof AMQPMessage ? $request->body : (string)$request;
        $this->responseBody = null;
        $this->requestQueue = $this->queueName;
        $this->correlationId = Utility::generateHash();

        $message = $request instanceof AMQPMessage ? $request : new AMQPMessage((string)$request);
        $message->set('reply_to', $this->responseQueue);
        $message->set('correlation_id', $this->correlationId);
        $message->set('timestamp', time());

        $this->channel->queue_declare(
            $this->requestQueue,
            false,
            false,
            false,
            false
        );

        $this->trigger('request.before.send', [$message]);

        $this->channel->basic_publish(
            $message,
            null,
            $this->requestQueue
        );

        $this->trigger('request.after.send', [$message]);

        while ($this->responseBody === null) {
            $this->channel->wait();
        }

        return $this->responseBody;
    }

    /**
     * Sends the passed request to the server using the passed queue.
     * Alias for `self::request()`.
     * @param string|AMQPMessage $request The request body or an `AMQPMessage` instance.
     * @param string|null $queueName [optional] The name of queue to send through.
     * @return string The response body.
     * @throws RPCEndpointException If the client is not connected yet or if request Correlation ID does not match the one of the response.
     */
    public function call($request, ?string $queueName = null): string
    {
        return $this->request($request, $queueName);
    }

    /**
     * Validates the response.
     * @param AMQPMessage $response
     * @return void
     */
    protected function onResponse(AMQPMessage $response): void
    {
        $this->trigger('response.on.get', [$response]);

        if ($this->correlationId === $response->get('correlation_id')) {
            $this->responseBody = $this->callback($response);
            $response->ack();
            return;
        }

        throw new RPCEndpointException(
            sprintf(
                'Correlation ID of the response "%s" does not match the one of the request "%s"!',
                $this->correlationId,
                (string)$response->get('correlation_id')
            )
        );
    }

    /**
     * Returns the final response body.
     * @return string
     */
    protected function callback(AMQPMessage $message): string
    {
        return $message->body;
    }
}
