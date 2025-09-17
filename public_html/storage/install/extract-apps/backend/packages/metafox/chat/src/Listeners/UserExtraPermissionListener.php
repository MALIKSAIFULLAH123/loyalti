<?php

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Traits\ChatTrait;
use MetaFox\Platform\Contracts\User;

class UserExtraPermissionListener
{
    use ChatTrait;

    /**
     * @param  User|null            $context
     * @param  User|null            $user
     * @return array<string, mixed>
     */
    public function handle(?User $context, ?User $user = null): array
    {
        return [
            'can_message' => $this->canMessage($context, $user),
        ];
    }
}
