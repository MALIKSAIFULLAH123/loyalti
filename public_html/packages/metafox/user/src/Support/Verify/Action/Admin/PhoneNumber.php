<?php

namespace MetaFox\User\Support\Verify\Action\Admin;

use MetaFox\User\Models\User;
use MetaFox\User\Models\UserVerify;

/**
 * Class PhoneNumber.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PhoneNumber extends AbstractAdminActionService
{
    public function send(User $user, string $verifiable): bool
    {
        $this->sendAbstract($user, UserVerify::ACTION_PHONE_NUMBER);

        app('user.verification')->sendVerificationPhoneNumber($user, $verifiable);

        return true;
    }

    public function verifyUser(User $user): bool
    {
        if ($user->hasVerifiedPhoneNumber()) {
            return false;
        }

        return $user->hasPhoneNumber();
    }

    public function verifySendingService(): void
    {
    }
}
