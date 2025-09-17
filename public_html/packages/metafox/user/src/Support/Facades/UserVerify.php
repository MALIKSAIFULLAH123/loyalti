<?php

namespace MetaFox\User\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\User\Contracts\UserVerifySupportContract;
use MetaFox\User\Support\UserVerifySupport;

/**
 * Class UserVerify.
 *
 * @method static string       getVerifyAtField(string $action)
 * @method static string       getVerifiableField(string $action)
 * @method static array<mixed> getAllowedActions(string $service)
 * @see UserVerifySupport
 */
class UserVerify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserVerifySupportContract::class;
    }
}
