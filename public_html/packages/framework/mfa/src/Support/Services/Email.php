<?php

namespace MetaFox\Mfa\Support\Services;

use MetaFox\Mfa\Jobs\SendEmailVerificationJob;
use MetaFox\Platform\Contracts\User;

/**
 * Class Email.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class Email extends OTPService
{
    public function isConfigurable(User $user): bool
    {
        return filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function send(User $user, string $code): bool
    {
        SendEmailVerificationJob::dispatch($user, $code);

        return true;
    }

    public function validateField(): array
    {
        return ['email'];
    }
}
