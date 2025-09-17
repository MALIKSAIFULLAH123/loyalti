<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Support\Traits\ChatplusTrait;
use MetaFox\Platform\Contracts\User;

class UserExtraPermissionListener
{
    use ChatplusTrait;

    /**
     * @param  User|null            $context
     * @param  User|null            $user
     * @return array<string, mixed>
     */
    public function handle(?User $context, ?User $user = null): array
    {
        return [
            'can_message' => $user ? $this->canMessage($context, $user) : false,
        ];
    }
}
