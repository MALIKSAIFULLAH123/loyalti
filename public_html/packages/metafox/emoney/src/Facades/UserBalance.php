<?php
namespace MetaFox\EMoney\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\EMoney\Contracts\UserBalanceSupportInterface;

/**
 * @method static float getMinValueForSending(float $currentBalance)
 * @method static float getMaxValueForSending(float $currentBalance)
 * @method static float getMinValueForReducing(float $currentBalance)
 * @method static float getMaxValueForReducing(float $currentBalance)
 * @method static float getRestAvailableAmountForAdjustment(float $currentBalance)
 * @method static array getAdjustmentTypeOptions()
 */
class UserBalance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserBalanceSupportInterface::class;
    }
}
