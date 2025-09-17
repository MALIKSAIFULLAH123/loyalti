<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Poll\Models\Poll;

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
        if ($itemType != Poll::ENTITY_TYPE) {
            return;
        }

        $item = Poll::find($itemId);
        if (!$item instanceof Poll) {
            return;
        }

        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
