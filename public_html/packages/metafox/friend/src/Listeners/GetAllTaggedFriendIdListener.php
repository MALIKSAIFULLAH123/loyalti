<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\HasTaggedFriend;

class GetAllTaggedFriendIdListener
{
    public function handle(HasTaggedFriend $item): array
    {
        return resolve(TagFriendRepositoryInterface::class)->getTaggedUserIdsByItem($item);
    }
}
