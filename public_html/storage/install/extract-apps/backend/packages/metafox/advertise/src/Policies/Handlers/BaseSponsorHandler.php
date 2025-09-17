<?php

namespace MetaFox\Advertise\Policies\Handlers;

use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;

class BaseSponsorHandler
{
    protected const SPONSOR_POLICY_METHOD = 'sponsorItem';

    protected function validateResourceStatus(Content $resource, bool $isFeed = false): bool
    {
        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->isDraft()) {
            return false;
        }

        if (!is_array($resource->toSponsorData())) {
            return false;
        }

        /**
         * If sponsor in feed, then validate pending status of feed.
         */
        $item = $resource;

        if ($isFeed) {
            if (!$resource instanceof ActivityFeedSource) {
                return false;
            }

            if (null === $resource->activity_feed) {
                return false;
            }

            $item = $resource->activity_feed;
        }

        if (Facade::isPendingSponsor($item)) {
            return false;
        }

        return true;
    }

    protected function validateValue(Content $resource, int $newValue, bool $isFeed = false): bool
    {
        if (!in_array($newValue, [0, 1])) {
            return false;
        }

        $compared = match ($isFeed) {
            true    => $resource->sponsor_in_feed,
            default => $resource->is_sponsor
        };

        if ($newValue == $compared) {
            return false;
        }

        return true;
    }

    protected function validateCreatePermission(User $user): bool
    {
        if (!$user->hasPermissionTo('advertise_sponsor.create')) {
            return false;
        }

        return true;
    }

    protected function validatePermissionOnResource(User $user, Content $resource): bool
    {
        $policy = PolicyGate::getPolicyFor(get_class($resource));

        if (!is_object($policy)) {
            return true;
        }

        $sponsorMethod = self::SPONSOR_POLICY_METHOD;

        if (!method_exists($policy, $sponsorMethod)) {
            return true;
        }

        return $policy->$sponsorMethod($user, $resource);
    }
}
