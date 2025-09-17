<?php

namespace MetaFox\ActivityPoint\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\ActivityPoint\Contracts\Support\PointSetting as PointSettingContract;

/**
 * @method static array getAllowedRoleOptions()
 * @method static array getAllowedRole()
 */
class PointSetting extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PointSettingContract::class;
    }
}
