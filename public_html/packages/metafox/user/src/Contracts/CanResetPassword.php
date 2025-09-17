<?php

namespace MetaFox\User\Contracts;

use Illuminate\Contracts\Auth\CanResetPassword as LaravelResetPasswordInterface;
use MetaFox\User\Models\PasswordResetToken;

interface CanResetPassword extends LaravelResetPasswordInterface
{
    /**
     * @param  PasswordResetToken $token
     * @param  string             $channel
     * @param  string             $as
     * @return void
     */
    public function sendPasswordResetToken(PasswordResetToken $token, string $channel = 'mail', string $as = 'token'): void;

    /**
     * @return array<int , mixed>
     */
    public function getResetMethods(): array;
}
