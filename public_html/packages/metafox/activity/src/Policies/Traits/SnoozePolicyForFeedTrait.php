<?php

namespace MetaFox\Activity\Policies\Traits;

use MetaFox\Activity\Policies\SnoozePolicy;
use MetaFox\Platform\Contracts\User;

trait SnoozePolicyForFeedTrait
{
    public function snooze(User $user, ?User $owner): bool
    {
        // temporarily hide snooze feature
        return false;

        return policy_check(SnoozePolicy::class, 'snooze', $user, $owner);
    }

    public function snoozeOwner(User $user, ?User $owner): bool
    {
        // temporarily hide snooze feature
        return false;

        // In case already snoozed this owner
        if (!$this->snooze($user, $owner)) {
            return false;
        }

        // In case feed is belonged to page/group/event
        if ($owner->entityType() != $user->entityType()) {
            return false;
        }

        return true;
    }

    public function snoozeForever(User $user, ?User $owner): bool
    {
        return policy_check(SnoozePolicy::class, 'snoozeForever', $user, $owner);
    }
}
