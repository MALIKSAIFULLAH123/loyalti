<?php

namespace MetaFox\Mfa\Support\Services;

use MetaFox\Mfa\Jobs\SendSmsVerificationJob;
use MetaFox\Platform\Contracts\User;
use MetaFox\Sms\Support\Traits\PhoneNumberTrait;

/**
 * Class Sms.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class Sms extends OTPService
{
    use PhoneNumberTrait;

    public function isConfigurable(User $user): bool
    {
        $phoneNumber = $user->phone_number;

        if (!$phoneNumber) {
            return false;
        }

        return $this->validatePhoneNumber($phoneNumber);
    }

    public function send(User $user, string $code): bool
    {
        SendSmsVerificationJob::dispatch($user, $code);

        return true;
    }

    public function validateField(): array
    {
        return ['phone_number'];
    }
}
