<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Paypal\Support\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Paypal\Support\Paypal;
use RuntimeException;

/**
 * Trait SubscriptionWebhookTrait.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @mixin Paypal;
 */
trait SubscriptionWebhookTrait
{
    /**
     * verifySubscriptionWebhook.
     *
     * @param  array<mixed> $payload
     * @return array<mixed>
     */
    protected function verifySubscriptionWebhook(array $payload = []): array
    {
        $gatewaySubscriptionId = Arr::get($payload, 'resource.id');
        if (empty($gatewaySubscriptionId)) {
            throw new RuntimeException('Invalid subscription webhook.');
        }

        $gateway = $this->getGateway();
        $order = $this->orderRepository->getByGatewaySubscriptionId($gatewaySubscriptionId, $gateway->entityId());
        if (!$order instanceof Order) {
            throw new RuntimeException('The requested order can not be found.');
        }

        if (!$order->isRecurringOrder()) {
            throw new RuntimeException('The requested order is not a recurring order.');
        }

        // double check gateway subscription
        $gatewaySubscription = $this->getGatewaySubscription($gatewaySubscriptionId);
        if (!$gatewaySubscription) {
            throw new RuntimeException('The requested gateway subscription can not be found.');
        }

        return [$order, $gatewaySubscription];
    }

    /**
     * handleBillingSubscriptionActivated.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handleBillingSubscriptionActivated(array $payload = []): void
    {
        [$order, $gatewaySubscription] = $this->verifySubscriptionWebhook($payload);

        $status = Str::lower(Arr::get($gatewaySubscription, 'status'));
        if ($status != 'active') {
            throw new RuntimeException('The subscription status does not match.');
        }

        Payment::onSubscriptionActivated($order, $payload);
    }

    /**
     * handleBillingSubscriptionExpired.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handleBillingSubscriptionExpired(array $payload = []): void
    {
        [$order, $gatewaySubscription] = $this->verifySubscriptionWebhook($payload);

        $status = Str::lower(Arr::get($gatewaySubscription, 'status'));
        if ($status != 'expired') {
            throw new RuntimeException('The subscription status does not match.');
        }

        Payment::onSubscriptionExpired($order, $payload);
    }

    /**
     * handleBillingSubscriptionSuspended.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handleBillingSubscriptionSuspended(array $payload = []): void
    {
        [$order, $gatewaySubscription] = $this->verifySubscriptionWebhook($payload);

        $status = Str::lower(Arr::get($gatewaySubscription, 'status'));
        if ($status != 'suspended') {
            throw new RuntimeException('The subscription status does not match.');
        }

        Payment::onSubscriptionCancelled($order, $payload);
    }

    /**
     * handleBillingSubscriptionCancelled.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handleBillingSubscriptionCancelled(array $payload = []): void
    {
        [$order, $gatewaySubscription] = $this->verifySubscriptionWebhook($payload);

        $status = Str::lower(Arr::get($gatewaySubscription, 'status'));
        if ($status != 'cancelled') {
            throw new RuntimeException('The subscription status does not match.');
        }

        Payment::onSubscriptionCancelled($order, $payload);
    }

    /**
     * handleBillingSubscriptionPaymentFailed.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handleBillingSubscriptionPaymentFailed(array $payload = []): void
    {
        [$order, $gatewaySubscription] = $this->verifySubscriptionWebhook($payload);

        $status = Str::lower(Arr::get($gatewaySubscription, 'status'));
        if ($status != 'active') {
            throw new RuntimeException('The subscription status does not match.');
        }

        Payment::onRecurringPaymentFailure($order, $payload);
    }
}
