<?php

namespace MetaFox\Friend\Policies;

use MetaFox\Friend\Models\FriendList;
use MetaFox\Friend\Models\FriendList as Resource;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class FriendListPolicy.
 * @ignore
 * @codeCoverageIgnore
 */
class FriendListPolicy
{
    use HasPolicyTrait;

    protected string $type = FriendList::ENTITY_TYPE;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        //Does not allow view friend list in case you does not have permission for viewing friends
        if (!$user->hasPermissionTo('friend_list.view')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User     $user
     * @param resource $resource
     *
     * @return bool
     */
    public function view(User $user, Resource $resource): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        //Does not allow view friend list in case you does not have permission for viewing friends
        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        if (!$user->hasPermissionTo('friend_list.view')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('friend_list.create');
    }

    /**
     * @param User     $user
     * @param resource $resource
     *
     * @return bool
     */
    public function actionOnFriendList(User $user, Resource $resource): bool
    {
        if (!$user->hasPermissionTo('friend_list.create')) {
            return false;
        }

        if ($user->id != $resource->userId()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User     $user
     * @param resource $resource
     *
     * @return bool
     */
    public function update(User $user, Resource $resource): bool
    {
        if ($user->userId() != $resource->userId()) {
            return false;
        }

        return $user->hasPermissionTo('friend_list.update');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User            $user
     * @param  array<resource> $friendLists
     * @return bool
     */
    public function updateLists(User $user, array $friendLists): bool
    {
        if (!$user->hasPermissionTo('friend_list.update')) {
            return false;
        }

        foreach ($friendLists as $friendList) {
            if ($user->userId() != $friendList->userId()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User     $user
     * @param resource $resource
     *
     * @return bool
     */
    public function delete(User $user, Resource $resource): bool
    {
        if ($user->userId() != $resource->userId()) {
            return false;
        }

        return $user->hasPermissionTo('friend_list.delete');
    }
}
