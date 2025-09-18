<?php
namespace MetaFox\Subscription\Listeners;

use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;

class SubscriptionRecycledListener
{
    public function handle(Order $order, ?Transaction $transaction = null)
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

        resolve(SubscriptionInvoiceRepositoryInterface::class)->recycleInvoice($invoice);
    }
}
