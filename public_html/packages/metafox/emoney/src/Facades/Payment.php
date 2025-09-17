<?php

namespace MetaFox\EMoney\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\EMoney\Contracts\PaymentInterface;
use MetaFox\Payment\Models\Order;

/**
 * @method static array|null processPayment(Order $order, array $extra = [])
 * @method static string generateTransactionId(Order $order)
 * @method static string generateOrderId(Order $order)
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PaymentInterface::class;
    }
}
