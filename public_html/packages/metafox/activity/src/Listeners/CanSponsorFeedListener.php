<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class CanSponsorFeedListener
{
    public function handle(User $context, Content $resource): bool
    {
        if (!$resource instanceof ActivityFeedSource) {
            return false;
        }

        if (null === $resource->activity_feed) {
            return false;
        }

        if (!$context->hasPermissionTo(sprintf('%s.%s', $resource->activity_feed->entityType(), 'sponsor'))) {
            return false;
        }

        if (!$context->hasPermissionTo(sprintf('%s.%s', $resource->activity_feed->entityType(), 'sponsor_free'))) {
            return false;
        }

        return true;
    }
}
