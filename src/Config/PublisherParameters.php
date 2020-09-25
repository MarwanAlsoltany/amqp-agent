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
 * A subset of AmqpAgentParameters class for the Publisher class.
 * @since 1.2.0
 */
final class PublisherParameters extends AbstractParameters
{
    /**
     * The default exchange options that the worker should use when no overrides are provided.
     * @var array
     */
    public const EXCHANGE_OPTIONS = AmqpAgentParameters::EXCHANGE_OPTIONS;

    /**
     * The default bind options that the worker should use when no overrides are provided.
     * @var array
     */
    public const BIND_OPTIONS = AmqpAgentParameters::BIND_OPTIONS;

    /**
     * The default message options that the worker should use when no overrides are provided.
     * @var array
     */
    public const MESSAGE_OPTIONS = AmqpAgentParameters::MESSAGE_OPTIONS;

    /**
     * The default publish options that the worker should use when no overrides are provided.
     * @var array
     */
    public const PUBLISH_OPTIONS = AmqpAgentParameters::PUBLISH_OPTIONS;
}
