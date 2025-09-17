<?php
namespace MetaFox\EMoney\Listeners;

use MetaFox\EMoney\Facades\Payment as EwalletPayment;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Support\Gateway\EwalletPaymentGateway;
use MetaFox\EMoney\Support\Support;
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

        if ($gateway->service != EwalletPaymentGateway::GATEWAY_SERVICE_NAME) {
            return;
        }

        if (!$order->gateway_order_id) {
            return;
        }

        if ($order->status != Order::STATUS_PENDING_APPROVAL) {
            return;
        }

        $transaction = Transaction::query()
            ->where([
                'outgoing_order_id' => $order->gateway_order_id,
            ])
            ->first();

        if (null === $transaction) {
            return;
        }

        if ($transaction->status != Support::TRANSACTION_STATUS_APPROVED) {
            return;
        }

        Payment::onPaymentSuccess($order, [
            'id'       => EwalletPayment::generateTransactionId($order),
            'currency' => $order->currency,
            'amount'   => $order->total,
            'status'   => $order->status,
            'raw_data' => [],
        ], $result);
    }
}
