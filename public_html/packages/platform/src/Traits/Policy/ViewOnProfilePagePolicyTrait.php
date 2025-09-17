<?php

namespace MetaFox\Platform\Traits\Policy;

use MetaFox\Platform\Contracts\User as User;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Trait ViewOnProfilePagePolicyTrait.
 * @deprecated
 */
trait ViewOnProfilePagePolicyTrait
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
}
