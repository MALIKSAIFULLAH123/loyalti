<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Paypal\Support\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Paypal\Support\Paypal;
use RuntimeException;

/**
 * Trait PaymentWebhookTrait.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @mixin Paypal;
 * @property OrderRepositoryInterface $orderRepository
 */
trait PaymentWebhookTrait
{
    /**
     * verifyPaymentWebhook.
     *
     * @param  array<mixed> $payload
     * @return bool
     */
    protected function verifyPaymentWebhook(array $payload = []): bool
    {
        if (!Arr::has($payload, 'resource.id')) {
            throw new RuntimeException('Invalid payment webhook.');
        }

        // should we double check gateway transaction?
        // Not effective: as PayPal noted "It takes a maximum of three hours for executed
        // transactions to appear in the list transactions call."
        return true;
    }

    /**
     * verifyPaymentSaleWebhook.
     *
     * @param  array<mixed> $payload
     * @return array<mixed>
     */
    protected function verifyPaymentSaleWebhook(array $payload = []): array
    {
        $this->verifyPaymentWebhook($payload);

        $gatewaySubscriptionId = Arr::get($payload, 'resource.billing_agreement_id');
        if (empty($gatewaySubscriptionId)) {
            throw new RuntimeException('Invalid subscription payment webhook.');
        }

        // recurring payments
        $gateway = $this->getGateway();
        $order = $this->orderRepository->getByGatewaySubscriptionId($gatewaySubscriptionId, $gateway->entityId());
        if (!$order instanceof Order) {
            throw new RuntimeException('The requested order can not be found.');
        }

        $transaction = [
            'id' => Arr::get($payload, 'resource.id'),
            'currency' => Arr::get($payload, 'resource.amount.currency'),
            'amount' => Arr::get($payload, 'resource.amount.total'),
            'status' => Str::lower(Arr::get($payload, 'resource.state')),
            'raw_data' => $payload,
        ];

        return [$order, $transaction];
    }

    /**
     * verifyPaymentCaptureWebhook.
     *
     * @param  array<mixed> $payload
     * @return array<mixed>
     */
    protected function verifyPaymentCaptureWebhook(array $payload = []): array
    {
        $this->verifyPaymentWebhook($payload);

        $gatewayOrderId = Arr::get($payload, 'resource.supplementary_data.related_ids.order_id');
        if (empty($gatewayOrderId)) {
            throw new RuntimeException('Invalid onetime payment webhook.');
        }

        // onetime payments
        $gateway = $this->getGateway();
        $order = $this->orderRepository->getByGatewayOrderId($gatewayOrderId, $gateway->entityId());
        if (!$order instanceof Order) {
            throw new RuntimeException('The requested order can not be found.');
        }

        $transaction = [
            'id' => Arr::get($payload, 'resource.id'),
            'currency' => Arr::get($payload, 'resource.amount.currency_code'),
            'amount' => Arr::get($payload, 'resource.amount.value'),
            'status' => Str::lower(Arr::get($payload, 'resource.status')),
            'raw_data' => $payload,
        ];

        return [$order, $transaction];
    }

    /**
     * handlePaymentSaleCompleted.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handlePaymentSaleCompleted(array $payload = [])
    {
        [$order, $transaction] = $this->verifyPaymentSaleWebhook($payload);

        $status = Arr::get($transaction, 'status');
        if ($status != 'completed') {
            throw new RuntimeException('The transaction status does not match.');
        }

        Payment::onPaymentSuccess($order, $transaction, $payload);
    }

    /**
     * handlePaymentSalePending.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handlePaymentSalePending(array $payload = [])
    {
        [$order, $transaction] = $this->verifyPaymentSaleWebhook($payload);

        $status = Arr::get($transaction, 'status');
        if ($status != 'pending') {
            throw new RuntimeException('The transaction status does not match.');
        }

        Payment::onPaymentPending($order, $transaction, $payload);
    }

    /**
     * handlePaymentSaleDenied.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handlePaymentSaleDenied(array $payload = [])
    {
        [$order, $transaction] = $this->verifyPaymentSaleWebhook($payload);

        $status = Arr::get($transaction, 'status');
        if ($status != 'denied') {
            throw new RuntimeException('The transaction status does not match.');
        }

        Payment::onPaymentFailure($order, $transaction, $payload);
    }

    /**
     * handlePaymentSaleRefunded.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handlePaymentSaleRefunded(array $payload = [])
    {
        [$order, $transaction] = $this->verifyPaymentSaleWebhook($payload);

        $status = Arr::get($transaction, 'status');
        if (!in_array($status, ['refunded', 'partially_refunded'])) {
            throw new RuntimeException('The transaction status does not match.');
        }

        Payment::onPaymentFailure($order, $transaction, $payload);
    }

    /**
     * handlePaymentCaptureCompleted.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handlePaymentCaptureCompleted(array $payload = [])
    {
        [$order, $transaction] = $this->verifyPaymentCaptureWebhook($payload);

        $status = Arr::get($transaction, 'status');
        if ($status != 'completed') {
            throw new RuntimeException('The transaction status does not match.');
        }

        Payment::onPaymentSuccess($order, $transaction, $payload);
    }

    /**
     * handlePaymentCapturePending.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handlePaymentCapturePending(array $payload = [])
    {
        [$order, $transaction] = $this->verifyPaymentCaptureWebhook($payload);

        $status = Arr::get($transaction, 'status');
        if ($status != 'pending') {
            throw new RuntimeException('The transaction status does not match.');
        }

        Payment::onPaymentPending($order, $transaction, $payload);
    }

    /**
     * handlePaymentCaptureDenied.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handlePaymentCaptureDenied(array $payload = [])
    {
        [$order, $transaction] = $this->verifyPaymentCaptureWebhook($payload);

        $status = Arr::get($transaction, 'status');
        if ($status != 'denied') {
            throw new RuntimeException('The transaction status does not match.');
        }

        Payment::onPaymentFailure($order, $transaction, $payload);
    }

    /**
     * handlePaymentCaptureRefunded.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handlePaymentCaptureRefunded(array $payload = [])
    {
        [$order, $transaction] = $this->verifyPaymentCaptureWebhook($payload);

        $status = Arr::get($transaction, 'status');
        if (!in_array($status, ['refunded', 'partially_refunded'])) {
            throw new RuntimeException('The transaction status does not match.');
        }

        Payment::onPaymentFailure($order, $transaction, $payload);
    }
}
