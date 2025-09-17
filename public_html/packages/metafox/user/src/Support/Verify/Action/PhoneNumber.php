<?php

namespace MetaFox\User\Support\Verify\Action;

use MetaFox\Form\AbstractForm;
use MetaFox\User\Models\User;
use MetaFox\User\Exceptions\VerifyCodeException;
use MetaFox\User\Models\UserVerify;

/**
 * Class PhoneNumber.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PhoneNumber extends AbstractActionService
{
    public function verifyForm(User $resource, string $verifiable, string $verifyPlace, string $resolution = 'web'): ?AbstractForm
    {
        return $this->loadVerifyForm($resource, $verifiable, UserVerify::ACTION_PHONE_NUMBER, $verifyPlace, $resolution);
    }

    public function verify(?string $code, ?string $hash = null): ?User
    {
        return $this->verifyAbstract(UserVerify::ACTION_PHONE_NUMBER, $code, $hash);
    }

    public function editForm(User $resource, string $resolution = 'web'): ?AbstractForm
    {
        return $this->loadForm($resource, null, 'user.account.edit_phone_number', $resolution);
    }

    public function send(User $user, string $verifiable): bool
    {
        $this->sendAbstract($user, UserVerify::ACTION_PHONE_NUMBER);

        app('user.verification')->sendVerificationPhoneNumber($user, $verifiable);

        return true;
    }

    public function resend(User $user, string $verifiable): bool
    {
        return $this->resendAbstract($user, UserVerify::ACTION_PHONE_NUMBER, $verifiable);
    }

    public function mustVerify(User $user, array $extra = []): bool
    {
        return app('user.verification')->mustVerifyPhoneNumber($user->phone_number, $extra);
    }

    protected function homeVerify(User $user): void
    {
        if ($user->hasVerifiedPhoneNumber()) {
            throw new VerifyCodeException(['title' => __p('user::phrase.account_has_been_verified')]);
        }

        $user->markPhoneNumberAsVerified();
    }

    protected function updateAccountVerify(User $user, UserVerify $verify): void
    {
        $user->forceFill(['phone_number' => $verify->verifiable])->save();

        $user->markPhoneNumberAsVerified();
    }
}
