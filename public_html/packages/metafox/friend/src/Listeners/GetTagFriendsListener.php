<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Contracts\Database\Eloquent\Builder;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\HasTaggedFriend;

/**
 * Class GetTagFriendsListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetTagFriendsListener
{
    /**
     * @param HasTaggedFriend $item
     * @param int             $limit
     *
     * @return Builder
     */
    public function handle(HasTaggedFriend $item, int $limit, array $excludedIds = []): Builder
    {
        return resolve(TagFriendRepositoryInterface::class)->getTagFriends($item, $limit, $excludedIds);
    }
}
