<?php
namespace MetaFox\ActivityPoint\Support\Facade;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use MetaFox\ActivityPoint\Contracts\Support\PointConversionInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\ActivityPoint\Support\PointConversion as Support;

/**
 * @method static array getConversionCurrencies()
 * @method static int getTotalPointsPerDay(User $user)
 * @method static int getTotalPointsPerMonth(User $user)
 * @method static int getExchangedPointsInYear(User $user)
 * @method static int getExchangedPointsInMonth(User $user)
 * @method static int getAvailableUserPoints(User $user)
 * @method static float getConversionAmount(int $points, string $currency)
 * @method static float getCommissionFee(float $total)
 * @method static string|null getExchangeRateFormat(string $currency = Support::DEFAULT_CONVERSION_RATE_CURRENCY_TO_MONEY)
 * @method static float getCommissionPercentage()
 * @method static array getConversionRequestStatusOptions()
 * @method static int   aggregateConversionRequest(Carbon $start, Carbon $end)
 * @method static int getMinPointsCanCreate(User $user)
 * @method static int getMaxPointsCanCreate(User $user)
 * @method static int|null getRestPointsPerDay(User $user)
 * @method static int|null getRestPointsPerMonth(User $user)
 * @method static int getPendingConversionPoints(User $user)
 */
class PointConversion extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PointConversionInterface::class;
    }
}
