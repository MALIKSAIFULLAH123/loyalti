<?php

namespace MetaFox\Advertise\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanPurchaseSponsorInFeed extends BaseSponsorHandler implements PolicyRuleInterface
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

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        $can = app('events')->dispatch('activity.feed.can_purchase_sponsor', [$user, $resource], true);

        if (true !== $can) {
            return false;
        }

        if (!$this->validateFeedPrice($user, $resource)) {
            return false;
        }

        return $this->validateValue($resource, 1, true);
    }

    protected function validateFeedPrice(User $user, Content $resource): bool
    {
        $price = app('events')->dispatch('activity.feed.get_sponsor_price', [$user, $resource], true);

        if (null === $price) {
            return false;
        }

        return true;
    }
}
