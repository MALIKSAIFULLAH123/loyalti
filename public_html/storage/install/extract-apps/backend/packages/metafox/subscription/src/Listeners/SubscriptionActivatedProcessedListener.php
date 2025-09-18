<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

class SubscriptionActivatedProcessedListener
{
    public function handle(?Order $order, ?Transaction $transaction = null)
    {
        if ($order->itemType() != SubscriptionInvoice::ENTITY_TYPE) {
            return null;
        }

        $invoice = $order->item;

        if (null === $invoice) {
            return false;
        }

        if ($invoice->payment_status == Helper::getCompletedPaymentStatus()) {
            return false;
        }

        if ((float) $invoice->initial_price > 0 || $invoice->activeTransactions()->count()) {
            return false;
        }

        $params = [
            'total_paid'     => $invoice->initial_price,
            'transaction_id' => null,
        ];

        if ($transaction instanceof Transaction) {
            $params['transaction_id'] = $transaction->gateway_transaction_id;
        }

        resolve(SubscriptionInvoiceRepositoryInterface::class)->updatePayment($order->itemId(), Helper::getCompletedPaymentStatus(), $params);
    }
}
