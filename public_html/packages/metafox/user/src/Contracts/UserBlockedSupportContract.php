<?php

namespace MetaFox\User\Contracts;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\UserEntity;

/**
 * Interface UserBlockedSupportContract.
 */
interface UserBlockedSupportContract
{
    /**
     * @param  int  $userId
     *
     * @return string
     */
    public function getCacheName(int $userId): string;

    /**
     * Use for clearing cache of the user.
     *
     * @param  int  $userId
     */
    public function clearCache(int $userId): void;

    /**
     * @param  User|null  $user
     * @param  User|null  $owner
     *
     * @return bool
     */
    public function isBlocked(?User $user, ?User $owner): bool;

    /**
     * Get blocked users of the user.
     * Stored user ids only.
     *
     * @param  User         $user
     * @param  string|null  $search
     * @return array<int, int>
     */
    public function getBlockedUsers(User $user, string $search = null): array;

    /**
     * Get blocked user IDs of the user.
     * Stored user ids only.
     *
     * @param  User  $user
     *
     * @return int[]
     */
    public function getBlockedUserIds(User $user): array;

    /**
     * Block user.
     *
     * @param  User  $user
     * @param  User  $owner
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function blockUser(User $user, User $owner): bool;

    /**
     * Unblock the user.
     *
     * @param  User  $user
     * @param  User  $owner
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function unBlockUser(User $user, User $owner): bool;

    /**
     * Return a collection of the user entity.
     *
     * @param  User         $user
     * @param  string|null  $search
     * @return Collection
     */
    public function getBlockedUsersCollection(User $user, ?string $search): Collection;

    /**
     * Get user blocked detail based on user_id and owner_id.
     * Return an instance user entity.
     *
     * @param  User  $user
     * @param  User  $owner
     *
     * @return UserEntity|null
     */
    public function getBlockUserDetail(User $user, User $owner);
}
