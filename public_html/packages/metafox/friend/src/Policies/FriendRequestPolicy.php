<?php

namespace MetaFox\Friend\Policies;

use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class FriendRequestPolicy.
 * @ignore
 * @codeCoverageIgnore
 */
class FriendRequestPolicy
{
    use HasPolicyTrait;

    protected string $type = FriendRequest::ENTITY_TYPE;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
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
    public function sendRequest(User $user, User $owner): bool
    {
        if (!$user->hasPermissionTo('friend_request.create')) {
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

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool
    {
        return true;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return true;
    }
}
