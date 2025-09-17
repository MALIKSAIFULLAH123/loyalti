<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Payment\Models\Order;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

class SubscriptionCanceledProcessedListener
{
    public function handle(?Order $order)
    {
        if ($order->itemType() != SubscriptionInvoice::ENTITY_TYPE) {
            return null;
        }

        $invoice = $order->item;

        if (null === $invoice) {
            return false;
        }

        if (!$invoice->isCompleted()) {
            return false;
        }

        resolve(SubscriptionInvoiceRepositoryInterface::class)->updatePayment($order->itemId(), Helper::getCanceledPaymentStatus(), [
            'total_paid'     => null,
            'transaction_id' => null,
            'is_manual'      => false,
        ]);
    }
}
