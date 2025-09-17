<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Support\Carbon;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\Content;

class UpdateLatestActivityListener
{
    public function handle(?Content $content): void
    {
        $feed = $content?->activity_feed;
        if (!$feed instanceof Feed) {
            return;
        }

        $feed->updateQuietly([
            'latest_activity_at' => Carbon::now(),
        ]);
    }
}
