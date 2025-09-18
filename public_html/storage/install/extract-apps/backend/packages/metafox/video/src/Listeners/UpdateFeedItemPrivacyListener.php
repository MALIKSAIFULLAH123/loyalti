<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Models\Video;

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
        if ($itemType != Video::ENTITY_TYPE) {
            return;
        }

        $item = Video::find($itemId);
        if (!$item instanceof Video) {
            return;
        }

        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
