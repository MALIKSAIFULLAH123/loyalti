<?php

namespace MetaFox\Activity\Policies\Traits;

use MetaFox\Activity\Contracts\ActivityHiddenManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Facades\Settings;

trait HideFeedPolicyTrait
{
    public function hideFeed(User $user, Feed $feed): bool
    {
        if (Settings::get('activity.feed.enable_hide_feed', false) === false) {
            return false;
        }

        if (!$user->hasPermissionTo('feed.hide')) {
            return false;
        }

        // Cannot hide your self.
        if ($feed->userId() == $user->entityId()) {
            return false;
        }

        $service = resolve(ActivityHiddenManager::class);

        if ($service->isHide($user, $feed)) {
            return false;
        }

        return true;
    }

    public function unHideFeed(User $user, Feed $feed): bool
    {
        if (!$user->hasPermissionTo('feed.hide')) {
            return false;
        }

        $service = resolve(ActivityHiddenManager::class);

        if (!$service->isHide($user, $feed)) {
            return false;
        }

        return true;
    }
}
