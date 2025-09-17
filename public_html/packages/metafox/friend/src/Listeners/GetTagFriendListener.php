<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Models\TagFriend;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\User;

/**
 * Class GetTagFriendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetTagFriendListener
{
    /**
     * @param mixed     $item
     * @param User|null $friend
     *
     * @return ?TagFriend
     */
    public function handle($item, ?User $friend): ?TagFriend
    {
        if (!$item instanceof HasTaggedFriend) {
            return null;
        }

        if (!$friend instanceof User) {
            return null;
        }

        return resolve(TagFriendRepositoryInterface::class)->getTagFriend($item, $friend);
    }
}
