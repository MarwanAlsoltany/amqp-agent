<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Config;

use MAKS\AmqpAgent\Config\AbstractParameters;
use MAKS\AmqpAgent\Config\AmqpAgentParameters;

/**
 * A subset of AmqpAgentParameters class for RPC Endpoints class.
 * @since 2.0.0
 */
final class RPCEndpointParameters extends AbstractParameters
{
    /**
     * The default connection options that the `ServerEndpoint` and `ClientEndpoint` should use when no overrides are provided.
     * @var array
     */
    public const RPC_CONNECTION_OPTIONS = AmqpAgentParameters::RPC_CONNECTION_OPTIONS;

    /**
     * The default queue name that the `ServerEndpoint` and `ClientEndpoint` should use when no overrides are provided.
     * @var array
     */
    public const RPC_QUEUE_NAME = AmqpAgentParameters::RPC_QUEUE_NAME;
}
