<?php

namespace MetaFox\ActivityPoint\Policies;

use MetaFox\ActivityPoint\Models\PointStatistic as Resource;
use MetaFox\Platform\Contracts\User;

/**
 * Point Package Policy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StatisticPolicy
{
    public function view(User $user, Resource $resource): bool
    {
        if ($user->entityId() === $resource->entityId()) {
            return true;
        }

        return false;
    }

    public function giftPoint(User $user, User $owner): bool
    {
        if (!$user->hasPermissionTo('activitypoint.can_gift_activity_points')) {
            return false;
        }

        return true;
    }
}
