<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\Facades\Payment;

class PlaceOrderProcessedListener
{
    public function handle(Order $order, array $result): void
    {
        $gateway = Gateway::query()
            ->where('id', $order->gateway_id)
            ->first();

        if (null === $gateway) {
            return;
        }

        if ($gateway->service != 'activitypoint') {
            return;
        }

        if ($order->status != Order::STATUS_PENDING_APPROVAL) {
            return;
        }

        Payment::onPaymentSuccess($order, [
            'id'       => ActivityPoint::generateTransactionId($order),
            'currency' => $order->currency,
            'amount'   => $order->total,
            'status'   => $order->status,
            'raw_data' => [],
        ], $result);
    }
}
