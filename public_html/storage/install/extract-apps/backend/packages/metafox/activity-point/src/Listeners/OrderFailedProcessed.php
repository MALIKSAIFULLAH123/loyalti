<?php

namespace MetaFox\ActivityPoint\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\ActivityPoint\Repositories\PurchasePackageRepositoryInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;

/**
 * Class PointUpdatedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class OrderFailedProcessed
{
    /**
     * @param  Order       $order
     * @param  Transaction $transaction
     * @return void
     */
    public function handle(Order $order, Transaction $transaction): void
    {
        try {
            $purchase = resolve(PurchasePackageRepositoryInterface::class)->find($order->itemId());
            resolve(PointPackageRepositoryInterface::class)->onFailedPurchasePackage($purchase);
        } catch (Exception $error) {
            Log::info('Purchase does not exist!');
            Log::info($error->getMessage());
        }
    }
}
