<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;

/**
 * Class DeleteTagFriendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteTagFriendListener
{
    /**
     * @param int $id
     *
     * @return bool
     */
    public function handle(int $id): bool
    {
        return resolve(TagFriendRepositoryInterface::class)->deleteTagFriend($id);
    }
}
