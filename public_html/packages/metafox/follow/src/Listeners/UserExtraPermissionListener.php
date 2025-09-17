<?php

namespace MetaFox\Follow\Listeners;

use MetaFox\Follow\Support\Traits\IsFollowTrait;
use MetaFox\Platform\Contracts\User;

class UserExtraPermissionListener
{
    use IsFollowTrait;

    /**
     * @param  User      $context
     * @param  User|null $user
     * @return array
     */
    public function handle(User $context, ?User $user): array
    {
        if (null === $user) {
            return [];
        }

        return [
            'can_follow'   => $this->canFollow($context, $user),
            'can_unfollow' => $this->canUnfollow($context, $user),
        ];
    }
}
