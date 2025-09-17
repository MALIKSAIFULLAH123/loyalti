<?php

namespace MetaFox\Activity\Traits;

use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\User\Support\Facades\UserPrivacy;

trait ScheduledFeedExtra
{
    use HasExtra;

    protected function getScheduleFeedExtra()
    {
        $scheduledFeed = $this->resource;

        $permissions = [
            'can_send_now' => UserPrivacy::hasAccess($scheduledFeed->user, $scheduledFeed->owner, 'feed.share_on_wall')
        ];

        return $permissions;
    }
}
