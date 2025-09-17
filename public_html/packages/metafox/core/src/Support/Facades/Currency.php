<?php

namespace MetaFox\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Localize\Contracts\CurrencySupportContract;
use MetaFox\Localize\Support\Currency as CurrencySupport;
use MetaFox\Platform\Contracts\User;

/**
 * class Currency.
 *
 * @see CurrencySupport
 * @method static array getActiveOptions()
 * @method static string|null getName(?string $code)
 * @method static array getAllActiveCurrencies()
 * @method static array getCurrencies()
 * @method static string|null getUserCurrencyId(User $context)
 * @method static string|null getPriceFormatByCurrencyId(string $currencyId, float $price, ?string $precision = null)
 * @method static array rules(string $name, array $rules = [])
 */
class Currency extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CurrencySupportContract::class;
    }
}
