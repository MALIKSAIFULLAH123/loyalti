<?php
namespace MetaFox\Featured\Listeners;

use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;

class PendingPaymentListener
{
    public function handle(Order $order, ?Transaction $transaction = null)
    {
        if ($order->itemType() != Invoice::ENTITY_TYPE) {
            return;
        }

        resolve(InvoiceRepositoryInterface::class)->updatePendingPayment($order, $transaction?->gateway_transaction_id);
    }
}
