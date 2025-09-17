<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\FriendRepositoryInterface;

/**
 * Class CountTotalMutualFriendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CountTotalMutualFriendListener
{
    /**
     * @param int $contextId
     * @param int $userId
     *
     * @return int
     */
    public function handle(int $contextId, int $userId): int
    {
        return resolve(FriendRepositoryInterface::class)->countMutualFriends($contextId, $userId);
    }
}
