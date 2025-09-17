<?php
namespace MetaFox\Subscription\Listeners;

use MetaFox\Payment\Models\Order;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

class SubscriptionExpiredListener
{
    public function handle(?Order $order)
    {
        if (null === $order) {
            return;
        }

        if ($order->itemType() != SubscriptionInvoice::ENTITY_TYPE) {
            return;
        }

        /**
         * @var SubscriptionInvoice $invoice
         */
        $invoice = $order->item;

        if (null === $invoice) {
            return;
        }

        if ($invoice->isExpired()) {
            return;
        }

        resolve(SubscriptionInvoiceRepositoryInterface::class)->updatePayment($invoice->entityId(), Helper::getExpiredPaymentStatus());
    }
}
