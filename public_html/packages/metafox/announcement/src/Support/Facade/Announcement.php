<?php

namespace MetaFox\Announcement\Support\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getStyleOptions()
 * @method static array getAllowedRoleOptions()
 * @method static array getAllowedRole()
 */
class Announcement extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MetaFox\Announcement\Contracts\Support\Announcement::class;
    }
}
