<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Contracts;

use MetaFox\Platform\Contracts\User;

/**
 * Interface OTPServiceInterface.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface OTPServiceInterface
{
    /**
     * @return string
     */
    public function getVerifyCode(): string;

    /**
     * @param  User   $user
     * @param  string $code
     * @return bool
     */
    public function send(User $user, string $code): bool;
}
