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
 * A subset of AmqpAgentParameters class for the AbstractWorker class.
 * @since 1.2.0
 */
final class AbstractWorkerParameters extends AbstractParameters
{
    /**
     * The default prefix for naming that is used when no name is provided.
     * @var array
     */
    public const PREFIX = AmqpAgentParameters::PREFIX;

    /**
     * The default connection options that the worker should use when no overrides are provided.
     * @var array
     */
    public const CONNECTION_OPTIONS = AmqpAgentParameters::CONNECTION_OPTIONS;
    /**
     * The default channel options that the worker should use when no overrides are provided.
     * @var array
     */
    public const CHANNEL_OPTIONS = AmqpAgentParameters::CHANNEL_OPTIONS;

    /**
     * The default queue options that the worker should use when no overrides are provided.
     * @var array
     */
    public const QUEUE_OPTIONS = AmqpAgentParameters::QUEUE_OPTIONS;
}
