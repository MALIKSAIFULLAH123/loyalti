<?php
namespace MetaFox\Featured\Listeners;

use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;

class SuccessPaymentListener
{
    public function handle(Order $order, ?Transaction $transaction = null): void
    {
        if ($order->itemType() != Invoice::ENTITY_TYPE) {
            return;
        }

        resolve(InvoiceRepositoryInterface::class)->updateSuccessPayment($order, $transaction?->gateway_transaction_id);
    }
}
