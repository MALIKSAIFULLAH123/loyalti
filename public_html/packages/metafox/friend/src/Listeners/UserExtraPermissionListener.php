<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;

class UserExtraPermissionListener
{
    use IsFriendTrait;

    /**
     * @param  User      $context
     * @param  User|null $user
     * @return array
     */
    public function handle(?User $context, ?User $user): array
    {
        if (null === $user) {
            return [];
        }

        return [
            'can_add_friend' => $this->canAddFriend($context, $user),
        ];
    }
}
