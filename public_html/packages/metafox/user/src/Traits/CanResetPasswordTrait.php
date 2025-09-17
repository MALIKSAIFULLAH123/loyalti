<?php

namespace MetaFox\User\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use MetaFox\User\Contracts\CanResetPassword;
use MetaFox\User\Models\PasswordResetToken;
use MetaFox\User\Models\User;
use MetaFox\User\Notifications\ResetPasswordTokenNotification;

/**
 * @property Collection $resetTokens
 * @mixin CanResetPassword
 * @mixin User
 */
trait CanResetPasswordTrait
{
    public function sendPasswordResetToken(PasswordResetToken $token, string $channel = 'mail', string $as = 'token'): void
    {
        $this->notify(new ResetPasswordTokenNotification($token, $channel, $as));
    }

    public function resetTokens(): HasMany
    {
        return $this->hasMany(PasswordResetToken::class, 'user_id', 'id');
    }

    public function getEmailOptionForPasswordReset(): ?array
    {
        $email = $this->email;
        if (!$this->hasVerifiedEmail() || empty($email)) {
            return null;
        }

        return [
            'label' => parse_output()->maskedEmail($email),
            'value' => 'mail',
        ];
    }

    public function getPhoneNumberOptionForPasswordReset(): ?array
    {
        $phoneNumber = $this->phone_number;
        if (!$this->hasVerifiedPhoneNumber() || empty($phoneNumber)) {
            return null;
        }

        $fromLeft     = 3;
        $fromRight    = 2;
        $maskedLength = strlen($phoneNumber) - $fromLeft - $fromRight;

        return [
            'label' => Str::mask($phoneNumber, '*', $fromLeft, $maskedLength),
            'value' => 'sms',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getResetMethods(): array
    {
        $options = [
            $this->getEmailOptionForPasswordReset(),
            $this->getPhoneNumberOptionForPasswordReset(),
        ];

        return array_values(array_filter($options, fn ($option) => null !== $option));
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
    }
}
