<?php

namespace MetaFox\Activity\Policies;

use MetaFox\Activity\Support\Facades\Snooze;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

class SnoozePolicy
{
    public function snooze(User $user, ?User $owner): bool
    {
        if (!$this->snoozeForever($user, $owner)) {
            return false;
        }

        if (Snooze::isSnooze($user, $owner)) {
            return false;
        }

        return true;
    }

    public function snoozeForever(User $user, ?User $owner): bool
    {
        if (!$owner instanceof User) {
            return false;
        }

        if (!$user->hasPermissionTo('activity_snooze.create')) {
            return false;
        }

        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        if ($user->entityId() == $owner->entityId()) {
            return false;
        }

        if (Snooze::isSnoozeForever($user, $owner)) {
            return false;
        }

        return true;
    }

    public function unSnooze(User $user, ?User $owner): bool
    {
        if (!$owner instanceof User) {
            return false;
        }

        if (!$user->hasPermissionTo('activity_snooze.delete')) {
            return false;
        }

        if (!Snooze::isSnooze($user, $owner)) {
            return false;
        }

        return true;
    }
}
