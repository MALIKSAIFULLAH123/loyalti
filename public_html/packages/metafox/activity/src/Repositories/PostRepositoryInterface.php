<?php

namespace MetaFox\Activity\Repositories;

use MetaFox\Activity\Models\Post;
use MetaFox\Platform\Contracts\User;

interface PostRepositoryInterface
{
    /**
     * @param  User      $user
     * @param  User      $owner
     * @param  array     $params
     * @return Post|null
     */
    public function createPost(User $user, User $owner, array $params): ?Post;

    /**
     * @param  int  $userId
     * @return void
     */
    public function deleteUserData(int $userId): void;

    /**
     * @param  int  $ownerId
     * @return void
     */
    public function deleteOwnerData(int $ownerId): void;
}
