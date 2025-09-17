<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\User\Models\User;

/**
 * Class CountTotalFriendRequestListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CountTotalFriendRequestListener
{
    /**
     * @param User $user
     *
     * @return int
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        return resolve(FriendRequestRepositoryInterface::class)->countTotalFriendRequest($user);
    }
}
