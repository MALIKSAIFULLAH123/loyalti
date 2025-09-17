<?php

namespace MetaFox\User\Listeners;

use Illuminate\Http\Request;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as UserModel;

class UserLogoutListener
{
    /**
     * @param  User|null $user
     * @param  Request   $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $user, Request $request): void
    {
        if (!$user instanceof UserModel) {
            return;
        }

        //revoke current token
        $user->token()?->revoke();
    }
}
