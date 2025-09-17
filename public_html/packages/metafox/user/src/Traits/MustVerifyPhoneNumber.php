<?php

namespace MetaFox\User\Traits;

use MetaFox\User\Jobs\VerifyPhoneNumberJob;

trait MustVerifyPhoneNumber
{
    /**
     * Determine if the user has verified their phone number.
     *
     * @return bool
     */
    public function hasVerifiedPhoneNumber()
    {
        return null !== $this->phone_number_verified_at;
    }

    /**
     * Mark the given user's phone number as verified.
     *
     * @return bool
     */
    public function markPhoneNumberAsVerified()
    {
        return $this->forceFill([
            'phone_number_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Send the phone number verification notification.
     *
     * @return void
     */
    public function sendPhoneNumberVerificationNotification()
    {
        VerifyPhoneNumberJob::dispatch($this, $this->phone_number);
    }

    /**
     * Get the phone number address that should be used for verification.
     *
     * @return string
     */
    public function getPhoneNumberForVerification()
    {
        return $this->phone_number;
    }
}
