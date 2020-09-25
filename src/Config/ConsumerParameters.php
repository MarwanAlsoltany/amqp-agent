<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace MAKS\AmqpAgent\Config;

use MAKS\AmqpAgent\Config\AbstractParameters;
use MAKS\AmqpAgent\Config\AmqpAgentParameters;

/**
 * A subset of AmqpAgentParameters class for the Consumer class.
 * @since 1.2.0
 */
final class ConsumerParameters extends AbstractParameters
{
    /**
     * The default quality of service options that the worker should use when no overrides are provided.
     * @var array
     */
    public const QOS_OPTIONS = AmqpAgentParameters::QOS_OPTIONS;

    /**
     * The default wait options that the worker should use when no overrides are provided.
     * @var array
     */
    public const WAIT_OPTIONS = AmqpAgentParameters::WAIT_OPTIONS;

    /**
     * The default consume options that the worker should use when no overrides are provided.
     * @var array
     */
    public const CONSUME_OPTIONS = AmqpAgentParameters::CONSUME_OPTIONS;

    /**
     * The default acknowledgment options that the worker should use when no overrides are provided.
     * @var array
     */
    public const ACK_OPTIONS = AmqpAgentParameters::ACK_OPTIONS;

    /**
     * The default unacknowledgment options that the worker should use when no overrides are provided.
     * @var array
     */
    public const NACK_OPTIONS = AmqpAgentParameters::NACK_OPTIONS;

    /**
     * The default get options that the worker should use when no overrides are provided.
     * @var array
     */
    public const GET_OPTIONS = AmqpAgentParameters::GET_OPTIONS;

    /**
     * The default cancel options that the worker should use when no overrides are provided.
     * @var array
     */
    public const CANCEL_OPTIONS = AmqpAgentParameters::CANCEL_OPTIONS;

    /**
     * The default recover options that the worker should use when no overrides are provided.
     * @var array
     */
    public const RECOVER_OPTIONS = AmqpAgentParameters::RECOVER_OPTIONS;

    /**
     * The default reject options that the worker should use when no overrides are provided.
     * @var array
     */
    public const REJECT_OPTIONS = AmqpAgentParameters::REJECT_OPTIONS;
}
