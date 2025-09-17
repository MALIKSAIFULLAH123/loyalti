<?php

namespace MetaFox\User\Support\Verify\Action;

use MetaFox\Form\AbstractForm;
use MetaFox\User\Models\User;
use MetaFox\User\Exceptions\VerifyCodeException;
use MetaFox\User\Models\UserVerify;

/**
 * Class Email.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class Email extends AbstractActionService
{
    public function verifyForm(User $resource, string $verifiable, string $verifyPlace, string $resolution = 'web'): ?AbstractForm
    {
        return $this->loadVerifyForm($resource, $verifiable, UserVerify::ACTION_EMAIL, $verifyPlace, $resolution);
    }

    public function verify(?string $code, ?string $hash = null): ?User
    {
        return $this->verifyAbstract(UserVerify::ACTION_EMAIL, $code, $hash);
    }

    public function editForm(User $resource, string $resolution = 'web'): ?AbstractForm
    {
        return $this->loadForm($resource, null, 'user.account.edit_email', $resolution);
    }

    public function send(User $user, string $verifiable): bool
    {
        $this->sendAbstract($user, UserVerify::ACTION_EMAIL);

        app('user.verification')->sendVerificationEmail($user, $verifiable);

        return true;
    }

    public function resend(User $user, string $verifiable): bool
    {
        return $this->resendAbstract($user, UserVerify::ACTION_EMAIL, $verifiable);
    }

    public function mustVerify(User $user, array $extra = []): bool
    {
        return app('user.verification')->mustVerifyEmail($user->email, $extra);
    }

    protected function homeVerify(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new VerifyCodeException(['title' => __p('user::phrase.account_has_been_verified')]);
        }

        $user->markEmailAsVerified();
    }

    protected function updateAccountVerify(User $user, UserVerify $verify): void
    {
        $user->forceFill(['email' => $verify->verifiable])->save();

        $user->markEmailAsVerified();
    }
}
