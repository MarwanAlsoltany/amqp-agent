<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\RPC;

use PhpAmqpLib\Message\AMQPMessage;
use MAKS\AmqpAgent\Helper\ClassProxy;
use MAKS\AmqpAgent\RPC\AbstractEndpoint;
use MAKS\AmqpAgent\RPC\ServerEndpointInterface;
use MAKS\AmqpAgent\Exception\RPCEndpointException;

/**
 * A class specialized in responding. Implementing only the methods needed for a server.
 *
 * Example:
 * ```
 * $serverEndpoint = new ServerEndpoint();
 * $serverEndpoint->on('some.event', function () { ... });
 * $serverEndpoint->connect();
 * $serverEndpoint->respond('Namespace\SomeClass::someMethod', 'queue.name');
 * $serverEndpoint->disconnect();
 * ```
 *
 * @since 2.0.0
 * @api
 */
class ServerEndpoint extends AbstractEndpoint implements ServerEndpointInterface
{
    /**
     * The callback to use when processing the requests.
     * @var callable
     */
    protected $callback;


    /**
     * Listens on requests coming via the passed queue and processes them with the passed callback.
     * @param callable|null $callback [optional] The callback to process the request. This callback will be passed an `AMQPMessage` and must return a string.
     * @param string|null $queueName [optional] The name of the queue to listen on.
     * @return string The last processed request.
     * @throws RPCEndpointException If the server is not connected yet or if the passed callback didn't return a string.
     */
    public function respond(?callable $callback = null, ?string $queueName = null): string
    {
        $this->callback = $callback ?? [$this, 'callback'];
        $this->queueName = $queueName ?? $this->queueName;

        if ($this->isConnected()) {
            $this->requestQueue = $this->queueName;

            $this->channel->queue_declare(
                $this->requestQueue,
                false,
                false,
                false,
                false
            );

            $this->channel->basic_qos(
                null,
                1,
                null
            );

            $this->channel->basic_consume(
                $this->requestQueue,
                null,
                false,
                false,
                false,
                false,
                function ($message) {
                    ClassProxy::call($this, 'onRequest', $message);
                }
            );

            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }

            return $this->requestBody;
        }

        throw new RPCEndpointException('Server is not connected yet!');
    }

    /**
     * Listens on requests coming via the passed queue and processes them with the passed callback.
     * Alias for `self::respond()`.
     * @param callable|null $callback [optional] The callback to process the request. This callback will be passed an `AMQPMessage` and must return a string.
     * @param string|null $queueName [optional] The queue to listen on.
     * @return string The last processed request.
     * @throws RPCEndpointException If the server is not connected yet or if the passed callback didn't return a string.
     */
    public function serve(?callable $callback = null, ?string $queueName = null): string
    {
        return $this->respond($callback, $queueName);
    }

    /**
     * Replies to the client.
     * @param AMQPMessage $request
     * @return void
     */
    protected function onRequest(AMQPMessage $request): void
    {
        $this->trigger('request.on.get', [$request]);

        $this->requestBody = $request->body;
        $this->responseBody = call_user_func($this->callback, $request);
        $this->responseQueue = (string)$request->get('reply_to');
        $this->correlationId = (string)$request->get('correlation_id');

        if (!is_string($this->responseBody)) {
            throw new RPCEndpointException(
                sprintf(
                    'The passed processing callback must return a string, instead it returned (data-type: %s)!',
                    gettype($this->responseBody)
                )
            );
        }

        $message = new AMQPMessage($this->responseBody);
        $message->set('correlation_id', $this->correlationId);
        $message->set('timestamp', time());

        $this->trigger('response.before.send', [$message]);

        $request->getChannel()->basic_publish(
            $message,
            null,
            $this->responseQueue
        );

        $request->ack();

        $this->trigger('response.after.send', [$message]);
    }

    /**
     * Returns the final request body. This method will be ignored if a callback in `self::respond()` is specified.
     * @return string
     */
    protected function callback(AMQPMessage $message): string
    {
        return $message->body;
    }
}
