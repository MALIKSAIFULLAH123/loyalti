<?php

namespace MetaFox\User\Support\Verify\Action\Admin;

use MetaFox\User\Models\User;
use MetaFox\User\Models\UserVerify;

/**
 * Class Email.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class Email extends AbstractAdminActionService
{
    public function send(User $user, string $verifiable): bool
    {
        $this->sendAbstract($user, UserVerify::ACTION_EMAIL);

        app('user.verification')->sendVerificationEmail($user, $verifiable);

        return true;
    }

    public function verifyUser(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        return $user->hasEmailAddress();
    }

    public function verifySendingService(): void
    {
    }
}
