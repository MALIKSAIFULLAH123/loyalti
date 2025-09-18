<?php

namespace MetaFox\Marketplace\Listeners;

use MetaFox\Marketplace\Models\Invoice;
use MetaFox\Marketplace\Repositories\InvoiceRepositoryInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;

class PaymentPendingListener
{
    public function handle(Order $order, ?Transaction $transaction = null)
    {
        if ($order->itemType() != Invoice::ENTITY_TYPE) {
            return null;
        }

        $transactionId = null;

        if (null !== $transaction) {
            $transactionId = $transaction->gateway_transaction_id;
        }

        resolve(InvoiceRepositoryInterface::class)->updatePendingPayment($order->itemId(), $transactionId);
    }
}
