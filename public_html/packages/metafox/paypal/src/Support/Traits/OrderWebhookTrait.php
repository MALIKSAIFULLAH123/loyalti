<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Paypal\Support\Traits;

use Illuminate\Support\Arr;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Paypal\Support\Paypal;
use RuntimeException;
use Srmklive\PayPal\Services\PayPal as ServicesPayPal;

/**
 * Trait OrderWebhookTrait.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Paypal;
 * @property OrderRepositoryInterface $orderRepository
 */
trait OrderWebhookTrait
{
    /**
     * verifyOrderWebhook.
     *
     * @param  array<mixed> $payload
     * @return array<mixed>
     */
    protected function verifyOrderWebhook(array $payload = []): array
    {
        $gatewayOrderId = Arr::get($payload, 'resource.id');
        if (empty($gatewayOrderId)) {
            throw new RuntimeException('Invalid order webhook.');
        }

        $gateway = $this->getGateway();
        $order = $this->orderRepository->getByGatewayOrderId($gatewayOrderId, $gateway->entityId());
        if (!$order instanceof Order) {
            throw new RuntimeException('The requested order can not be found.');
        }

        // double check gateway order
        $gatewayOrder = $this->getGatewayOrder($gatewayOrderId);
        if (!$gatewayOrder) {
            throw new RuntimeException('The requested gateway order can not be found.');
        }

        return [$order, $gatewayOrder];
    }

    /**
     * handleCheckoutOrderApproved.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    protected function handleCheckoutOrderApproved(array $payload = [])
    {
        [$order, $gatewayOrder] = $this->verifyOrderWebhook($payload);

        $status = Arr::get($gatewayOrder, 'status');
        if ($status != 'APPROVED') {
            throw new RuntimeException('The order status does not match.');
        }

        Payment::onPaymentPending($order);

        $this->captureAuthorizedOrder($order->gateway_order_id);
    }

    /**
     * captureAuthorizedOrder.
     *
     * @param  string               $gatewayOrderId
     * @return array<string, mixed>
     */
    protected function captureAuthorizedOrder(string $gatewayOrderId): array
    {
        /** @var ServicesPayPal $service */
        $service = $this->getProvider();
        $service->getAccessToken();
        $result = $service->capturePaymentOrder($gatewayOrderId);

        if (!is_array($result) || Arr::get($result, 'status') != 'COMPLETED') {
            throw new RuntimeException('Could not capture authorized order.');
        }

        return [
            'status' => true,
            'gateway_order_id' => $gatewayOrderId,
        ];
    }
}
