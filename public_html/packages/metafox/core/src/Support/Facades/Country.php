<?php

namespace MetaFox\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Localize\Contracts\CountrySupportContract;
use MetaFox\Localize\Support\Country as CountrySupport;
use MetaFox\Platform\Contracts\User;

/**
 * class Country.
 *
 * @method static array       getAllActiveCountries()
 * @method static void        clearCache()
 * @method static array       getCountries()
 * @method static array|null  getCountry(string $countryIso)
 * @method static string|null getCountryName(string $countryIso)
 * @method static array       buildCountrySearchForm()
 * @method static array       getCountryStates(string $countryIso)
 * @method static string      getCountryStateName(string $countryIso, string $stateIso)
 * @method static string      getDefaultCountryIso()
 * @method static string      getDefaultCountryStateIso()
 * @method static array       getStatesSuggestions(User $context, array $params)
 * @method static array       getAllActiveStates(mixed $pluck = null)
 *
 * @see  CountrySupport
 * @link CountrySupportContract
 */
class Country extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CountrySupportContract::class;
    }
}
