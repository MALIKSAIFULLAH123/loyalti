<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Models\Link;

/**
 * Class UpdateFeedItemPrivacyListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateFeedItemPrivacyListener
{
    /**
     * @param int    $itemId
     * @param string $itemType
     * @param int    $privacy
     * @param int[]  $list
     */
    public function handle(int $itemId, string $itemType, int $privacy, array $list = []): void
    {
        if ($itemType != Link::ENTITY_TYPE) {
            return;
        }

        $item = Link::find($itemId);
        if (!$item instanceof Link) {
            return;
        }

        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
