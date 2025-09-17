<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\User;

/**
 * Class CheckPrivacyOnlyMeListener.
 * @ignore
 */
class CheckPrivacyOnlyMeListener
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

        if ($owner instanceof Event) {
            if ($owner->isAdmin($user)) {
                return true;
            }
        }

        return null;
    }
}
