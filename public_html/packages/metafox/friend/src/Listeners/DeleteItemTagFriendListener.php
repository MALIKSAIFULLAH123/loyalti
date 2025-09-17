<?php
namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\HasTaggedFriend;

class DeleteItemTagFriendListener
{
    public function handle(HasTaggedFriend $item, ?array $friendIds = null): void
    {
        resolve(TagFriendRepositoryInterface::class)->deleteItemTagFriends($item, $friendIds);
    }
}
