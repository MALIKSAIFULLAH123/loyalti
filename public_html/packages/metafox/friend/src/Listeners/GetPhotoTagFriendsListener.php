<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\HasTaggedFriend;

/**
 * Class GetPhotoTagFriendsListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetPhotoTagFriendsListener
{
    /**
     * @param  $item
     *
     * @return Builder[]|Collection
     */
    public function handle($item)
    {
        if (!$item instanceof HasTaggedFriend) {
            return [];
        }
        return resolve(TagFriendRepositoryInterface::class)->getItemTagFriends($item);
    }
}
