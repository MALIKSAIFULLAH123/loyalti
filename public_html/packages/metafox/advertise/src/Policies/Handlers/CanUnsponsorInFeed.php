<?php

namespace MetaFox\Advertise\Policies\Handlers;

use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanUnsponsorInFeed extends BaseSponsorHandler implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if ($newValue != 0) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource instanceof ActivityFeedSource) {
            return false;
        }

        if (!$this->validateValue($resource, 0, true)) {
            return false;
        }

        if (!$user->hasPermissionTo(sprintf('%s.%s', $entityType, 'sponsor_in_feed'))) {
            return false;
        }

        $canFree = app('events')->dispatch('activity.feed.can_sponsor_free', [$user, $resource], true);

        if ($canFree) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        $canPurchase = app('events')->dispatch('activity.feed.can_purchase_sponsor', [$user, $resource], true);

        if ($canPurchase) {
            return true;
        }

        return false;
    }
}
