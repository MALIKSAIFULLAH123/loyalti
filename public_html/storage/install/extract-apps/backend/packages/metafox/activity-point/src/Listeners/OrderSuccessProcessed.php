<?php

namespace MetaFox\ActivityPoint\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use MetaFox\ActivityPoint\Models\PackagePurchase;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\ActivityPoint\Repositories\PurchasePackageRepositoryInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;

/**
 * Class PointUpdatedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class OrderSuccessProcessed
{
    public function __construct(
        protected PurchasePackageRepositoryInterface $purchasePackageRepository,
        protected PointPackageRepositoryInterface    $pointPackageRepository,
    ) {}

    /**
     * @param Order       $order
     * @param Transaction $transaction
     * @return void
     */
    public function handle(Order $order, Transaction $transaction): void
    {
        if ($order->itemType() != PackagePurchase::ENTITY_TYPE) {
            return;
        }

        try {
            $purchase = $this->purchasePackageRepository->find($order->itemId());
            $this->pointPackageRepository->onSuccessPurchasePackage($purchase, $transaction->gateway_transaction_id);
        } catch (Exception $error) {
            Log::info('Purchase does not exist!');
            Log::info($error->getMessage());
        }
    }
}
