<?php

namespace MetaFox\Mfa\Listeners;

use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Support\Facades\MfaEnforcer;

class MfaServiceActivatedListener
{
    public function handle($userService): void
    {
        if (!$userService instanceof UserService) {
            return;
        }

        MfaEnforcer::onUserServiceActivated($userService);
    }
}
