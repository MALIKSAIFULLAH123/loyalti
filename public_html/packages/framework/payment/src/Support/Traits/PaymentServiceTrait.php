<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Support\Traits;

use Illuminate\Support\Arr;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Payment\Support\Payment;
use RuntimeException;

/**
 * Trait PaymentServiceTrait.
 * @mixin Payment
 */
trait PaymentServiceTrait
{
    public function onPaymentSuccess(Order $order, array $transactionData = [], ?array $data = []): void
    {
        if (empty($transactionData)) {
            throw new RuntimeException('The request does not contain transaction data');
        }

        app('events')->dispatch('payment.payment_success_processing', [$order, $transactionData, $data]);

        $order->refresh();

        Arr::set($transactionData, 'status', Transaction::STATUS_COMPLETED);

        $transaction = $this->transactionRepository->handleTransactionData($order, $transactionData);

        if (!$order->isStatusCompleted()) {
            $order->status = Order::STATUS_COMPLETED;
            $order->save();
            app('events')->dispatch('payment.payment_success', [$order, $transaction, $data]);
        }

        app('events')->dispatch('payment.payment_success_processed', [$order, $transaction]);
    }

    public function onPaymentPending(Order $order, ?array $transactionData = [], ?array $data = []): void
    {
        if (!$order->canUpdateToStatus(Order::STATUS_PENDING_PAYMENT)) {
            throw new RuntimeException('Unable to change the order status to ' . Order::STATUS_PENDING_PAYMENT);
        }

        app('events')->dispatch('payment.payment_pending_processing', [$order, $transactionData, $data]);

        $order->refresh();

        $transaction = null;
        if (is_array($transactionData) && Arr::has($transactionData, 'id')) {
            // not all failed payment contains transaction data
            Arr::set($transactionData, 'status', Transaction::STATUS_PENDING);
            $transaction = $this->transactionRepository->handleTransactionData($order, $transactionData);
        }

        if (!$order->isStatusPendingPayment()) {
            $order->status = Order::STATUS_PENDING_PAYMENT;
            $order->save();

            app('events')->dispatch('payment.payment_pending', [$order, $transaction]);
        }

        app('events')->dispatch('payment.payment_pending_processed', [$order, $transaction]);
    }

    public function onPaymentFailure(Order $order, ?array $transactionData = [], ?array $data = []): void
    {
        if (!$order->canUpdateToStatus(Order::STATUS_FAILED)) {
            throw new RuntimeException('Unable to change the order status to ' . Order::STATUS_FAILED);
        }

        app('events')->dispatch('payment.payment_failure_processing', [$order, $transactionData, $data]);

        $order->refresh();

        $transaction = null;
        if (is_array($transactionData) && Arr::has($transactionData, 'id')) {
            // not all failed payment contains transaction data
            Arr::set($transactionData, 'status', Transaction::STATUS_FAILED);
            $transaction = $this->transactionRepository->handleTransactionData($order, $transactionData);
        }

        if (!$order->isStatusFailed()) {
            $order->status = Order::STATUS_FAILED;
            $order->save();

            app('events')->dispatch('payment.payment_failure', [$order, $transaction]);
        }

        app('events')->dispatch('payment.payment_failure_processed', [$order, $transaction]);
    }
}
