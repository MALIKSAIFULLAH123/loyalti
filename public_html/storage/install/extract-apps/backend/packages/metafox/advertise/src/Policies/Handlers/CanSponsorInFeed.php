<?php

namespace MetaFox\Advertise\Policies\Handlers;

use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanSponsorInFeed extends BaseSponsorHandler implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$this->validateCreatePermission($user)) {
            return false;
        }

        if (!$this->validateResourceStatus($resource, true)) {
            return false;
        }

        if (!$this->validatePermissionOnResource($user, $resource)) {
            return false;
        }

        if (!$user->hasPermissionTo(sprintf('%s.%s', $entityType, 'sponsor_in_feed'))) {
            return false;
        }

        $can = app('events')->dispatch('activity.feed.can_sponsor_free', [$user, $resource], true);

        if (true !== $can) {
            return false;
        }

        return $this->validateValue($resource, 1, true);
    }
}
