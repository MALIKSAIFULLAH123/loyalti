<?php

namespace MetaFox\InAppPurchase\Support;

use Illuminate\Support\Arr;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Payment\Contracts\HasSupportSubscription;
use MetaFox\Payment\Contracts\HasSupportWebhook;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\AbstractPaymentGateway;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Payment\Support\Traits\HasSupportSubscriptionTrait;
use MetaFox\PaymentProcessor\Contracts\HasProcessorInterface;
use MetaFox\PaymentProcessor\Support\Traits\HasProcessorTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use RuntimeException;

/**
 * Class Paypal.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class InAppPurchaseGateway extends AbstractPaymentGateway implements HasSupportSubscription, HasSupportWebhook, HasProcessorInterface
{
    use HasSupportSubscriptionTrait;
    use HasProcessorTrait;

    public static function getGatewayServiceName(): string
    {
        return '';
    }

    public function createGatewayOrder(Order $order, array $params = []): array
    {
        $data = $order->toGatewayOrder();

        if (!$data) {
            throw new RuntimeException('Invalid order.');
        }

        return [];
    }

    public function getGatewayTransaction(string $gatewayTransactionId): ?array
    {
        return null;
    }

    public function getGatewayOrder(string $gatewayOrderId): ?array
    {
        return null;
    }

    public function createGatewaySubscription(Order $order, array $params = []): array
    {
        $data = $order->toGatewayOrder();
        if (!$order->isRecurringOrder() || !$data) {
            throw new RuntimeException('Invalid recurring order.');
        }

        return [
            'status'                  => true,
            'gateway_subscription_id' => 'temp_' . $order->entityId(), // original_transaction_id or purchase_token
            'gateway_redirect_url'    => '',
            'gateway_token'           => $order->entityId(), // Gateway token is order id, use to verify receipt
        ];
    }

    public function cancelGatewaySubscription(Order $order): array
    {
        return ['status' => true]; // User must cancel on App Store
    }

    public function getGatewaySubscription(string $gatewaySubscriptionId): ?array
    {
        return null;
    }

    public function getWebhookUrl(): string
    {
        return '';
    }

    public function verifyWebhook(array $payload): bool
    {
        return true;
    }

    public function handleWebhook(array $payload): bool
    {
        return true;
    }

    public function hasAccess(User $context, array $params): bool
    {
        $gateway = Payment::getManager()->getGatewayByName('in-app-purchase');

        if (!$gateway->is_active) {
            return false;
        }

        if (!parent::hasAccess($context, $params)) {
            return false;
        }

        if (!app_active('metafox/in-app-purchase')) {
            return false;
        }

        if (!MetaFox::isMobile()) {
            return false;
        }

        $allowTypes = array_column(InAppPur::getProductTypes(true, true), 'value');

        if (!in_array(Arr::get($params, 'entity_type'), $allowTypes)) {
            return false;
        }

        return true;
    }
}
