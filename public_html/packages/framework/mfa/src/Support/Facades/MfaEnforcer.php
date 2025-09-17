<?php

namespace MetaFox\Mfa\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Mfa\Models\EnforceRequest;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Support\MfaEnforcer as SupportMfaEnforcer;
use MetaFox\User\Models\User;

/**
 * Class MfaEnforcer.
 * @method static void process(User $user)
 * @method static void validate(User $user)
 * @method static void onUserServiceActivated(UserService $service)
 * @method static void onEnforcerDisabled()
 * @method static void onRequestOverdue(EnforceRequest $enforceRequest)
 *
 * @see \MetaFox\Mfa\Support\SupportMfaEnforcer
 */
class MfaEnforcer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportMfaEnforcer::class;
    }
}
