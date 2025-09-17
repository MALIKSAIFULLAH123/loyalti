<?php

namespace MetaFox\Ban\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Ban\Contracts\SupportInterface;
use MetaFox\Ban\Contracts\TypeHandlerInterface;
use MetaFox\User\Models\User;

/**
 * @method static array                getAllowedBanRuleTypes()
 * @method static bool                 validate(string $type, mixed $value)
 * @method static void                 validateMultipleType(?User $user = null)
 * @method static void                 automaticBan(?User $user = null, mixed $value)
 * @method static bool                 validateEmail(mixed $value)
 * @method static bool                 validateIPAddress(mixed $value)
 * @method static bool                 isSupportBanUser(string $type)
 * @method static array                getValidationRules(string $type)
 * @method static array                getValidatedRules(string $type, array $dataCheck)
 * @method static array                validateWithReturnReason(string $type, mixed $value)
 * @method static TypeHandlerInterface resolveTypeHandler(string $type)
 */
class Ban extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportInterface::class;
    }
}
