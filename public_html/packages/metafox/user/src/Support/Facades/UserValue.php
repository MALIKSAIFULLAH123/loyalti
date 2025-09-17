<?php

namespace MetaFox\User\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User;

/**
 * Class UserValue.
 * @method static array    getUserValueSettings(User $user)
 * @method static bool     checkUserValueSettingByName(User $user, string $settingName)
 * @method static int|null getUserValueSettingByName(User $user, string $settingName)
 * @method static bool     updateUserValueSetting(User $user, array $params)
 * @mixin \MetaFox\User\Support\UserValue
 */
class UserValue extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'UserValue';
    }
}
