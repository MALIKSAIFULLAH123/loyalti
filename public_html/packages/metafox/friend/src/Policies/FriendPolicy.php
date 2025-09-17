<?php

namespace MetaFox\Friend\Policies;

use MetaFox\Platform\Contracts\HasPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class FriendPolicy.
 * @ignore
 * @codeCoverageIgnore
 */
class FriendPolicy implements HasPolicy
{
    use HasPolicyTrait;

    /**
     * Determine whether the user can view any models.
     *
     * @param User      $user
     * @param User|null $owner
     *
     * @return bool
     */
    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($owner && $user->entityId() != $owner->entityId()) {
            if ($owner->entityType() != 'user') {
                return false;
            }

            if (!UserPrivacy::hasAccess($user, $owner, 'friend.view_friend')) {
                return false;
            }

            // Check can view on owner.
            if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param User $owner
     *
     * @return bool
     */
    public function addFriend(User $user, User $owner): bool
    {
        if ($owner->entityType() != UserModel::ENTITY_TYPE) {
            return false;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'friend.send_request')) {
            return false;
        }

        return true;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'friend.view_friend')) {
            return false;
        }

        return true;
    }
}
