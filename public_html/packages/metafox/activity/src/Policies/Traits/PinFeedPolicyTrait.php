<?php

namespace MetaFox\Activity\Policies\Traits;

use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\User as User;

trait PinFeedPolicyTrait
{
    private function prepareCheckPin(User $user, ?User $owner, Feed $feed, ?bool $isProfileFeed = null): bool
    {
        if (!$user->hasPermissionTo('feed.pin')) {
            return false;
        }

        if ($owner && method_exists($owner, 'canPinFeed')) {
            return call_user_func([$owner, 'canPinFeed'], $user, $feed);
        }

        return true;
    }

    public function pinFeed(User $user, Feed $feed, ?bool $isProfileFeed = null): bool
    {
        if (!$this->prepareCheckPin($user, $feed->owner, $feed, $isProfileFeed)) {
            return false;
        }

        return true;
    }

    public function unPinFeed(User $user, Feed $feed, ?bool $isProfileFeed = null): bool
    {
        if (!$this->prepareCheckPin($user, $feed->owner, $feed, $isProfileFeed)) {
            return false;
        }

        return true;
    }
}
