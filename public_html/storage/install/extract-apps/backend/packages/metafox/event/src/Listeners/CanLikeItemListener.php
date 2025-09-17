<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Support\Facades\Event;
use MetaFox\Platform\Contracts\User;

/**
 * Class CheckPrivacyOnlyMeListener.
 * @ignore
 */
class CanLikeItemListener
{
    /**
     * @param  User|null $user
     * @param  User      $owner
     * @return ?bool
     */
    public function handle(?User $user, User $owner): ?bool
    {
        return Event::checkFeedReactingPermission($user, $owner);
    }
}
