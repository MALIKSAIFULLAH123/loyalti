<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Platform\Facades\LoadReduce;

/**
 * Class CountTotalFriendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CountTotalFriendListener
{
    /**
     * @param int $userId
     *
     * @return int
     */
    public function handle(int $userId): int
    {
        return LoadReduce::getEntity(
            'user',
            $userId,
            null
        )?->total_friend ?? 0;
    }
}
