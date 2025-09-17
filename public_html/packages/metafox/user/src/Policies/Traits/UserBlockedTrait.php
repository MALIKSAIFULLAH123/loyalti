<?php

namespace MetaFox\User\Policies\Traits;

use MetaFox\Platform\Contracts\User;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Support\Facades\UserBlocked;

/**
 * @mixin UserPolicy
 */
trait UserBlockedTrait
{
    public function blockUser(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('user.can_block_other_members')) {
            return false;
        }

        if ($owner instanceof User) {
            if (UserBlocked::isBlocked($user, $owner)) {
                return false; // Ok you are block this member.
            }

            if ($user->entityId() == $owner->entityId()) {
                return false; // You cannot block yourself.
            }

            if ($owner->canBeBlocked() == false) {
                return false;
            }
        }

        return true;
    }

    public function unBlockUser(User $user, ?User $owner = null): bool
    {
        if ($owner instanceof User) {
            if (!UserBlocked::isBlocked($user, $owner)) {
                return false;
            }

            if ($user->entityId() == $owner->entityId()) {
                return false; // You cannot un-block yourself.
            }
        }

        return true;
    }
}
