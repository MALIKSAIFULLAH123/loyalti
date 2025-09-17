<?php

namespace MetaFox\Activity\Policies\Contracts;

use MetaFox\Platform\Contracts\User;

interface SnoozePolicyForFeedInterface
{
    /**
     * @param  User      $user
     * @param  User|null $owner
     * @return bool
     */
    public function snooze(User $user, ?User $owner): bool;

    /**
     * @param  User      $user
     * @param  User|null $owner
     * @return bool
     */
    public function snoozeOwner(User $user, ?User $owner): bool;

    /**
     * @param  User      $user
     * @param  User|null $owner
     * @return bool
     */
    public function snoozeForever(User $user, ?User $owner): bool;
}
