<?php

namespace MetaFox\Activity\Policies\Contracts;

use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\User as User;

interface HideFeedPolicyInterface
{
    /**
     * Hide a feed.
     *
     * @param User $user
     * @param Feed $feed
     *
     * @return bool
     */
    public function hideFeed(User $user, Feed $feed): bool;

    /**
     * Un-Hide a feed.
     *
     * @param User $user
     * @param Feed $feed
     *
     * @return bool
     */
    public function unHideFeed(User $user, Feed $feed): bool;
}
