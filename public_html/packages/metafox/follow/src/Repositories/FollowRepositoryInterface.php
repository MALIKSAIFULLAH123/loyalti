<?php

namespace MetaFox\Follow\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Follow.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface FollowRepositoryInterface
{
    /**
     * @param User $user
     * @param User $owner
     */
    public function follow(User $user, User $owner): void;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Paginator
     */
    public function viewFollow(User $context, array $attributes): Paginator;

    /**
     * @param User $context
     * @param User $user
     *
     * @return bool
     */
    public function unfollow(User $context, User $user): bool;

    /**
     * @param int $contextId
     * @param int $userId
     *
     * @return bool
     */
    public function isFollow(int $contextId, int $userId): bool;

    /**
     * @param User $user
     *
     * @return int
     */
    public function totalFollowers(User $user): int;

    /**
     * @param User $user
     *
     * @return int
     */
    public function totalFollowing(User $user): int;

    /**
     * @param User $context
     *
     * @return Collection
     */
    public function getUserFollowers(User $context): Collection;

    /**
     * @param User $context
     *
     * @return Builder
     */
    public function getFollowerQuery(User $context): Builder;

    /**
     * @param User $context
     *
     * @return Builder
     */
    public function getFollowingQuery(User $context): Builder;
}
