<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;

class MarkAsPendingListener
{
    public function handle(Content $item): void
    {
        if (!$item instanceof ActivityFeedSource) {
            return;
        }

        if ($item->isApproved()) {
            return;
        }

        $feed = $item->activity_feed;

        if (null === $feed) {
            return;
        }

        $owner = $feed->owner;

        if (null === $owner) {
            return;
        }

        if (!$owner->hasPendingMode()) {
            return;
        }

        if (!$feed->is_denied) {
            return;
        }

        $feed->is_pending = true;

        $feed->save();
    }
}
