<?php

namespace MetaFox\Translation\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Translation\Support\Translation as SupportTranslation;

/**
 * Class Translation.
 * @method static ?GatewayForm getGatewayAdminFormById(int $gatewayId);
 */
class Translation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SupportTranslation::class;
    }
}
