<?php

namespace MetaFox\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Localize\Contracts\CountryCitySupportContract;
use MetaFox\Localize\Models\CountryCity as Model;

/**
 * class CountryCity.
 *
 * @method static array      getCitySuggestions(array $params)
 * @method static Model|null getCity(string $cityCode)
 * @method static void       clearCache()
 * @method static string     getDefaultCityCode()
 * @method static array      getAllActiveCities(mixed $pluck = null)
 *
 * @see CountryCitySupportContract
 */
class CountryCity extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CountryCitySupportContract::class;
    }
}
