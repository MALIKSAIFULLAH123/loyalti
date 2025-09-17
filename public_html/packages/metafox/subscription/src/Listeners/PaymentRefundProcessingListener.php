<?php

namespace MetaFox\Subscription\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Payment\Models\Order;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

class PaymentRefundProcessingListener
{
    public function handle(?Order $order, ?array $transaction)
    {
        if ($order->itemType() != SubscriptionInvoice::ENTITY_TYPE) {
            return null;
        }

        if (!is_array($transaction) || Arr::get($transaction, 'status') != 'refunded') {
            return false;
        }

        resolve(SubscriptionInvoiceRepositoryInterface::class)->updatePayment($order->itemId(), Helper::getCanceledPaymentStatus(), [
            'total_paid'     => null,
            'transaction_id' => null,
        ]);
    }
}
