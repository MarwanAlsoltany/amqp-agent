<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2020
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\AmqpAgent\Worker;

/**
 * A trait containing the implementation of members mutation functions.
 * @since 1.0.0
 */
trait WorkerMutationTrait
{
    /**
     * The last mutation happened to a class member (for debugging purposes).
     * @var array
     */
    protected $mutation = [];

    /**
     * Mutates a subset of an array (class property) and returns the replaced subset.
     * @param string $member The name of the property.
     * @param array $overrides An associative array of the overrides.
     * @return array
     */
    protected function mutateClassMember(string $member, array $overrides): array
    {
        return $this->mutateClass($member, null, $overrides);
    }

    /**
     * Mutates a subset of an array inside a class property (nested array inside a property) and returns the replaced subset.
     * @param string $member The name of the property.
     * @param string $sub The key which under the array stored.
     * @param array $overrides An associative array of the overrides.
     * @return array
     */
    protected function mutateClassSubMember(string $member, string $sub, array $overrides): array
    {
        return $this->mutateClass($member, $sub, $overrides);
    }

    /**
     * Mutates a class property nested or not and returns the replaced subset.
     * @param string $member The name of the property.
     * @param string|null $sub [optional] The key which under the array stored.
     * @param array $overrides An associative array of the overrides.
     * @return array
     */
    private function mutateClass(string $member, ?string $sub, array $overrides): array
    {
        $changes = [];
        $signature = '@UNKNOWN[%s]';

        foreach ($overrides as $key => $value) {
            if ($sub) {
                if (isset($this->{$member}[$sub][$key])) {
                    $changes[$key] = $this->{$member}[$sub][$key];
                } else {
                    $changes[$key] = sprintf($signature, $key);
                }
                if ($value === sprintf($signature, $key)) {
                    unset($this->{$member}[$sub][$key]);
                } else {
                    $this->{$member}[$sub][$key] = $value;
                }
            } else {
                if (isset($this->{$member}[$key])) {
                    $changes[$key] = $this->{$member}[$key];
                } else {
                    $changes[$key] = sprintf($signature, $key);
                }
                if ($value === sprintf($signature, $key)) {
                    unset($this->{$member}[$key]);
                } else {
                    $this->{$member}[$key] = $value;
                }
            }
        }

        $this->mutation = [
            'member'    =>    $member,
            'old'       =>    $changes,
            'new'       =>    $overrides,
        ];

        return $changes;
    }
}
