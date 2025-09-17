<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Observers;

use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Support\Facades\MfaEnforcer;
use MetaFox\User\Models\User;

class UserServiceObserver
{
    /**
     * @param UserService $model
     */
    public function deleted(UserService $model): void
    {
        $model->userVerifyCodes()->delete();

        $this->handleEnforcer($model);
    }

    private function handleEnforcer(UserService $model)
    {
        $user = $model->user;
        if (!$user instanceof User) {
            return;
        }

        MfaEnforcer::process($user);
    }
}
