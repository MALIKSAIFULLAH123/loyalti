<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;

class PaymentSuccessProcessedListener
{
    public function handle(?Order $order, ?Transaction $transaction)
    {
        if ($order->itemType() != SubscriptionInvoice::ENTITY_TYPE) {
            return null;
        }

        $params = [
            'total_paid'     => null,
            'currency'       => null,
            'transaction_id' => null,
        ];

        if (null !== $transaction) {
            $params = array_merge($params, [
                'total_paid'     => $transaction->amount,
                'currency'       => $transaction->currency,
                'transaction_id' => $transaction->gateway_transaction_id,
            ]);
        }

        resolve(SubscriptionInvoiceRepositoryInterface::class)->updatePayment($order->itemId(), $order->status, $params);
    }
}
