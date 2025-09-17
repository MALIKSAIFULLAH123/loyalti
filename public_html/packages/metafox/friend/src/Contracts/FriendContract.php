<?php

namespace MetaFox\Friend\Contracts;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;

interface FriendContract
{
    public function getFriendship(User $context, User $user): int;

    /**
     * @param  int   $userId
     * @return array
     */
    public function getFriendIds(int $userId): array;

    /**
     * @param  User $user
     * @param  User $owner
     * @return bool
     */
    public function isFriend(User $user, User $owner): bool;

    /**
     * @param  array      $ids
     * @return Collection
     */
    public function getUsersForMention(array $ids): Collection;
}
