<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Friend\Models\TagFriend;

/**
 * Class GetTagFriendBuilderByItemTypeListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetTagFriendBuilderByItemTypeListener
{
    /**
     * @param string $itemType
     *
     * @return null|Builder
     */
    public function handle(string $itemType): ?Builder
    {
        return TagFriend::query()->where('item_type', $itemType);
    }
}
