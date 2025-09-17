<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Platform\Contracts\HasFeed;
use MetaFox\Platform\Facades\LoadReduce;

class CreateFeedFromResourceListener
{
    /**
     * @param  Model       $model
     * @param  string|null $fromResource
     * @return Feed|null
     */
    public function handle(Model $model, ?string $fromResource = Feed::FROM_APP_RESOURCE): ?Feed
    {
        $feed = ActivityFeed::createFeedFromFeedSource($model, $fromResource);
        if ($model instanceof HasFeed) {
            LoadReduce::flush();
            $model->load('activity_feed');
        }

        return $feed;
    }
}
