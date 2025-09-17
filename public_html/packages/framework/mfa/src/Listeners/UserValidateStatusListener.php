<?php

namespace MetaFox\Mfa\Listeners;

use MetaFox\Mfa\Support\Facades\MfaEnforcer;
use MetaFox\User\Models\User;

class UserValidateStatusListener
{
    public function handle($user): void
    {
        if (!$user instanceof User) {
            return;
        }

        MfaEnforcer::validate($user);
    }
}
