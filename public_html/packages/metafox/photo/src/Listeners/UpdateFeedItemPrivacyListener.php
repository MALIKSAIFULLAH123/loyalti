<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\PhotoGroup;

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
        if ($itemType != PhotoGroup::ENTITY_TYPE) {
            return;
        }

        $item = PhotoGroup::find($itemId);
        if (!$item instanceof PhotoGroup) {
            return;
        }

        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
