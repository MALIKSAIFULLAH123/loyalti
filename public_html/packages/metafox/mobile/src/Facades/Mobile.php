<?php

namespace MetaFox\Mobile\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Mobile\Contracts\SupportInterface;

/**
 * @method static array getSmartBannerPositionOptions()
 * @method static array getAllowSmartBannerPosition()
 */
class Mobile extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportInterface::class;
    }
}
