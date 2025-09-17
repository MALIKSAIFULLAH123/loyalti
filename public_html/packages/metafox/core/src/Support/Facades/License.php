<?php

namespace MetaFox\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Core\Support\License as SupportLicense;

/**
 * class License.
 * @method static array detail()
 * @method static array refresh()
 * @method static bool  isActive()
 * @method static void  deactivate()
 * @see SupportLicense
 */
class License extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SupportLicense::class;
    }
}
