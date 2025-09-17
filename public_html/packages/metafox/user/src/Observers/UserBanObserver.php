<?php

namespace MetaFox\User\Observers;

use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\UserBan;

/**
 * Class UserBanObserver.
 */
class UserBanObserver
{
    public function created(UserBan $userBan): void
    {
        $owner = $userBan->owner;
        // Push user to banned role.
        if (!$owner->hasRole(UserRole::BANNED_USER)) {
            $owner->syncRoles(UserRole::BANNED_USER);
        }
    }

    public function updated(UserBan $userBan): void
    {
        $owner = $userBan->owner;
        // Push user to banned role.
        if (!$owner->hasRole(UserRole::BANNED_USER)) {
            $owner->syncRoles(UserRole::BANNED_USER);
        }
    }

    public function deleted(UserBan $userBan): void
    {
        $owner = $userBan->owner;
        /** @var Role $role */
        $role = Role::findById($userBan->return_user_group);
        // Push user to return user role.
        $owner->syncRoles($role->name);
    }
}

// end stub
