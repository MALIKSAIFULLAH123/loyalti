<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\TagFriendModel;

/**
 * Class GetTagFriendByIdListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetTagFriendByIdListener
{
    /**
     * @param int $id
     *
     * @return TagFriendModel
     */
    public function handle(int $id): TagFriendModel
    {
        return resolve(TagFriendRepositoryInterface::class)->find($id);
    }
}
