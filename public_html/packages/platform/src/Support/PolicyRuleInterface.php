<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use MetaFox\Platform\Contracts\User;

/**
 * Interface PolicyCheckInterface
 * Check by function or policy handler.
 */
interface PolicyRuleInterface
{
    /**
     * This method invoked by PolicyGate::check.
     *
     * @param string     $entityType
     * @param User       $user
     * @param mixed|null $resource
     * @param int|null   $newValue
     *
     * @return bool|null
     */
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool;
}
