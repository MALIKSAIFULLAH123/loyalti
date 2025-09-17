<?php

namespace MetaFox\User\Listeners;

use MetaFox\User\Models\User;

class UserSignedInListener
{
    /**
     * @param  User|null            $user
     * @param  array<string, mixed> $params
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $user, array $params = []): void
    {
        if (!$user instanceof User) {
            return;
        }

        $this->updateActivityPoint($user);
    }

    protected function updateActivityPoint(User $user): void
    {
        app('events')->dispatch('activitypoint.increase_user_point', [$user, $user, 'sign_in']);
    }
}
