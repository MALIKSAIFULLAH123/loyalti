<?php

namespace MetaFox\User\Policies\Traits;

use MetaFox\Platform\Contracts\User;

trait UserPokeTrait
{
    public function poke(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('user.poke')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($user->entityId() == $owner->entityId()) {
                return false; // You cannot poke yourself.
            }
        }

        return true;
    }
}
