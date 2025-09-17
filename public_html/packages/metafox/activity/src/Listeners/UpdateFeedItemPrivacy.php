<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;
use MetaFox\Platform\Contracts\HasPrivacy;

/**
 * Class UpdateFeedItemPrivacy.
 * @ignore
 */
class UpdateFeedItemPrivacy
{
    /**
     * @param  int             $itemId
     * @param  string          $itemType
     * @param  int             $privacy
     * @param  int[]           $list
     * @return HasPrivacy|null
     */
    public function handle(int $itemId, string $itemType, int $privacy, array $list = []): ?HasPrivacy
    {
        $item = match ($itemType) {
            Share::ENTITY_TYPE => Share::find($itemId),
            Post::ENTITY_TYPE => Post::find($itemId),
            default => null,
        };

        if (!$item instanceof  HasPrivacy) {
            return null;
        }

        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();

        return $item;
    }
}
