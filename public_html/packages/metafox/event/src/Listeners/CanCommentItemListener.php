<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Support\Facades\Event;
use MetaFox\Platform\Contracts\User;

/**
 * Class CheckPrivacyOnlyMeListener.
 * @ignore
 */
class CanCommentItemListener
{
    /**
     * @param  User|null $user
     * @param  User|null $owner
     * @return ?bool
     */
    public function handle(?User $user, ?User $owner): ?bool
    {
        if (!$user || !$owner) {
            return false;
        }

        return Event::checkFeedReactingPermission($user, $owner);
    }
}
