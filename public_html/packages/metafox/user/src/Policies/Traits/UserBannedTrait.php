<?php

namespace MetaFox\User\Policies\Traits;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;

trait UserBannedTrait
{
    public function banUser(User $user, User $owner): bool
    {
        if (!$this->manage($user, $owner)) {
            return false;
        }

        if ($user->entityId() === $owner->entityId()) {
            return false;
        }

        // Current user cannot do ban if not admin or super admin.
        if (!$user->hasAnyRole([UserRole::ADMIN_USER, UserRole::SUPER_ADMIN_USER, UserRole::STAFF_USER])) {
            return false;
        }

        // Admin + Super Admin cannot be banned.
        if (!$user->hasAnyRole([UserRole::SUPER_ADMIN_USER])
            && $owner->hasAnyRole([UserRole::ADMIN_USER, UserRole::SUPER_ADMIN_USER])) {
            return false;
        }

        return true;
    }
}
