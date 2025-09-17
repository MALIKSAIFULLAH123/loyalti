<?php

namespace MetaFox\Follow\Policies;

use MetaFox\Follow\Support\Traits\IsFollowTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPolicy;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class FollowPolicy.
 * @ignore
 * @codeCoverageIgnore
 */
class FollowPolicy implements HasPolicy
{
    use IsFollowTrait;
    use HasPolicyTrait;

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param User $owner
     *
     * @return bool
     */
    public function addFollow(User $user, User $owner): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        if (!$owner instanceof HasPrivacyMember) {
            if (!UserPrivacy::hasAccess($user, $owner, 'follow.add_follow')) {
                return false;
            }
        }

        if ($owner->entityId() == $user->entityId()) {
            return false;
        }

        return true;
    }
    public function unfollow(User $user, User $owner): bool
    {
        return $this->canUnfollow($user, $owner);
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'follow.view_following')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'friend.view_friend')) {
            return false;
        }

        return true;
    }

    public function notifyFollowers(?User $context, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if ($resource->owner instanceof HasPrivacyMember) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if (!method_exists($resource, 'toFollowerNotification')) {
            return false;
        }

        if (null === $resource->toFollowerNotification()) {
            return false;
        }

        if ($resource instanceof HasTotalItem) {
            return false;
        }

        return true;
    }
}
