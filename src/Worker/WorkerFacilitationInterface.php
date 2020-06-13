<?php
/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */


namespace MAKS\AmqpAgent\Worker;

/**
 * An interface defining the simplest API to operate a worker.
 * @since 1.0.0
 */
interface WorkerFacilitationInterface
{
    /**
     * Executes all essential methods the worker needs before running its prime method.
     * @return self
     */
    public function prepare(): self;

    /**
     * A function that takes the entire overhead of running a worker and wraps it in one single method with a possibilty to change only to the prime parameter of the worker.
     * @return bool True on finish.
     */
    public function work($parameter): bool;
}
