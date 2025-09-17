<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Policy;

use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Trait HasPolicyTrait.
 */
trait HasPolicyTrait
{
    /**
     * @param User $user
     * @param User $owner
     *
     * @return bool
     */
    public function viewOnProfilePage(User $user, User $owner): bool
    {
        return UserPrivacy::hasAccess($user, $owner, 'profile.view_profile');
    }

    /**
     * @param string       $ability
     * @param array<mixed> $arguments
     *
     * @return bool
     */
    public function __call(string $ability, array $arguments): bool
    {
        try {
            $entityType = PolicyGate::getModelFor(get_class($this));
        } catch (\Throwable $exception) {
            return true;
        }

        return PolicyGate::check($entityType, $ability, $arguments);
    }
}
