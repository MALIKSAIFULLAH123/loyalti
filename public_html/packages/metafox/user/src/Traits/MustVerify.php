<?php

namespace MetaFox\User\Traits;

use Illuminate\Auth\MustVerifyEmail;
use MetaFox\Platform\Facades\Settings;

trait MustVerify
{
    use MustVerifyEmail;
    use MustVerifyPhoneNumber;

    /**
     * Check whether the user needs to verify their registered email / phone number.
     * @return bool
     */
    public function mustVerify(): bool
    {
        return $this->mustVerifyEmailAddress() || $this->mustVerifyPhoneNumber();
    }

    /**
     * Check whether the user needs to verify their phone number.
     * @return bool
     */
    public function mustVerifyPhoneNumber(): bool
    {
        if (!Settings::get('user.enable_sms_registration')) {
            return false;
        }

        return $this->shouldVerifyPhoneNumber();
    }

    /**
     * Check whether the user should to verify their phone number.
     * @return bool
     */
    public function shouldVerifyPhoneNumber(): bool
    {
        return $this->hasPhoneNumber() && !$this->hasVerifiedPhoneNumber();
    }

    /**
     * Check whether the user needs to verify their email address.
     * @return bool
     */
    public function mustVerifyEmailAddress(): bool
    {
        if (!Settings::get('user.verify_email_at_signup')) {
            return false;
        }

        return $this->shouldVerifyEmailAddress();
    }

    /**
     * Check whether the user should to verify their email address.
     * @return bool
     */
    public function shouldVerifyEmailAddress(): bool
    {
        return $this->hasEmailAddress() && !$this->hasVerifiedEmail();
    }

    /**
     * Mark the user as verified.
     * @return void
     */
    public function markAsVerified(): void
    {
        $this->forceFill([
            'verified_at' => $this->freshTimestamp(),
        ])->save();

        app('events')->dispatch('user.verified', [$this]);
        app('events')->dispatch('user.signup_new_friend', [$this]);
    }

    /**
     * Determine if the user has verified their account.
     *
     * @return bool
     */
    public function hasVerified(): bool
    {
        return null !== $this->verified_at;
    }

    /**
     * Check if the user has an associated phone number.
     * @return bool
     */
    public function hasPhoneNumber(): bool
    {
        return !empty($this->phone_number);
    }

    /**
     * Check if the user has an associated email address.
     * @return bool
     */
    public function hasEmailAddress(): bool
    {
        return !empty($this->email);
    }
}
