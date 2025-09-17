<?php

namespace MetaFox\User\Policies\Traits;

use MetaFox\Platform\Contracts\User;

trait UserAvatarTrait
{
    public function uploadAvatar(User $user, ?User $owner = null): bool
    {
        $userType = $user->entityType();

        if ($user->hasPermissionTo("$userType.moderate")) {
            return true;
        }

        if (!$user->hasPermissionTo('photo.create')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($user->entityId() != $owner->entityId()) {
                return false;
            }
        }

        return $user->hasPermissionTo("$userType.update");
    }

    public function setProfileAvatar(User $user, ?User $owner = null): bool
    {
        if (!$this->uploadAvatar($user, $owner)) {
            return false;
        }

        if (!$user->hasPermissionTo('photo.set_profile_avatar')) {
            return false;
        }

        return true;
    }
}
