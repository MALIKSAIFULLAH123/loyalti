<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Platform\Support\FeedAction;


class CreateFeedListener
{
    public function handle(FeedAction $action): ?Feed
    {
        return ActivityFeed::createActivityFeed($action);
    }
}
