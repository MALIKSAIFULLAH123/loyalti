<?php

namespace MetaFox\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Localize\Repositories\TimezoneRepositoryInterface;

/**
 * class Timezone.
 * @method static array       getActiveOptions()
 * @method static string|null getName(?int $id)
 * @method static string|null getTimezoneByName(?string $name)
 * @method static int         getDefaultTimezoneId()
 * @see TimezoneRepositoryInterface
 */
class Timezone extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TimezoneRepositoryInterface::class;
    }
}
