<?php

namespace MetaFox\EMoney\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\EMoney\Contracts\SupportInterface;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Support\Support;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\User;

/**
 * @method static float getMinimumWithdrawalAmount(string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE)
 * @method static string    getDefaultCurrency()
 * @method static User|null getNotifiable()
 * @method static User|Collection getNotifiables()
 * @method static array     getRequestStatusOptions()
 * @method static array     getRequestStatuses()
 * @method static array     getTransactionStatusOptions()
 * @method static array     getBaseCurrencyOptions(?string $target = null)
 * @method static string    getAppAlias()
 * @method static bool      isUsingNewAlias()
 * @method static array     getSourceOptions()
 * @method static array     getTypeOptions()
 * @method static array     getTransactionStatusInfo(string $status)
 * @method static array     getTransactionBalanceInfo(Transaction $transaction)
 * @method static string    getKeyPrice($currency)
 * @method static array     getWithdrawalRequestParams(User $context)
 * @method static string    getPaymentBalanceCurrency(Order $order, array $extra = [])
 */
class Emoney extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SupportInterface::class;
    }
}
