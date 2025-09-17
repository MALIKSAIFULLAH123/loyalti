<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Contracts;

use MetaFox\Form\AbstractForm;
use MetaFox\Payment\Http\Resources\v1\Gateway\Admin\GatewayForm;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\User;
use RuntimeException;

/**
 * Class Payment.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface ServiceInterface
{
    /**
     * getManager.
     *
     * @return GatewayManagerInterface
     */
    public function getManager(): GatewayManagerInterface;

    /**
     * getGatewayAdminFormById.
     *
     * @param  int          $gatewayId
     * @return ?GatewayForm
     */
    public function getGatewayAdminFormById(int $gatewayId): ?GatewayForm;

    /**
     * getGatewayAdminFormByName.
     *
     * @param  string       $formName
     * @return ?GatewayForm
     */
    public function getGatewayAdminFormByName(string $formName): ?GatewayForm;

    /**
     * initOrder.
     *
     * @param  IsBillable $billable
     * @return Order
     */
    public function initOrder(IsBillable $billable): Order;

    /**
     * place recurring/onetime order
     * will be placed accordingly to the payment_type in toOrder().
     *
     * @param  Order            $order
     * @param  int              $gatewayId
     * @param  array<mixed>     $params    additional parameters
     * @return array<mixed>
     * @throws RuntimeException
     */
    public function placeOrder(Order $order, int $gatewayId, array $params = []): array;

    /**
     * cancelSubscription.
     *
     * @param  Order            $order
     * @return array<mixed>
     * @throws RuntimeException
     */
    public function cancelSubscription(Order $order): array;

    /**
     * onSubscriptionActivated.
     *
     * @param  Order            $order
     * @param  ?array<mixed>    $data
     * @return void
     * @throws RuntimeException
     */
    public function onSubscriptionActivated(Order $order, ?array $data = []): void;

    /**
     * onSubscriptionExpired.
     *
     * @param  Order            $order
     * @param  ?array<mixed>    $data
     * @return void
     * @throws RuntimeException
     */
    public function onSubscriptionExpired(Order $order, ?array $data = []): void;

    /**
     * onSubscriptionCancelled.
     *
     * @param  Order            $order
     * @param  ?array<mixed>    $data
     * @return void
     * @throws RuntimeException
     */
    public function onSubscriptionCancelled(Order $order, ?array $data = []): void;

    /**
     * onRecurringPaymentFailure.
     *
     * @param  Order            $order
     * @param  ?array<mixed>    $data
     * @return void
     * @throws RuntimeException
     */
    public function onRecurringPaymentFailure(Order $order, ?array $data = []): void;

    /**
     * onPaymentSuccess.
     *
     * @param  Order            $order
     * @param  array<mixed>     $transactionData
     * @param  ?array<mixed>    $data
     * @return void
     * @throws RuntimeException
     */
    public function onPaymentSuccess(Order $order, array $transactionData = [], ?array $data = []): void;

    /**
     * onPaymentPending.
     *
     * @param  Order            $order
     * @param  ?array<mixed>    $transactionData
     * @param  ?array<mixed>    $data
     * @return void
     * @throws RuntimeException
     */
    public function onPaymentPending(Order $order, ?array $transactionData = [], ?array $data = []): void;

    /**
     * onPaymentFailure.
     *
     * @param  Order            $order
     * @param  ?array<mixed>    $transactionData
     * @param  ?array<mixed>    $data
     * @return void
     * @throws RuntimeException
     */
    public function onPaymentFailure(Order $order, ?array $transactionData = [], ?array $data = []): void;

    /**
     * onWebhook.
     *
     * @param  array<mixed> $payload
     * @return void
     */
    public function onWebhook(array $payload = []): void;

    /**
     * @param  Order      $order
     * @param  array|null $data
     * @param  array|null $transactionData
     * @return mixed
     */
    public function onSubscriptionRecycled(Order $order, ?array $data = [], ?array $transactionData = null);

    /**
     * @param  Order $order
     * @param  int   $gatewayId
     * @param  int   $payeeId
     * @param  array $params
     * @return array
     */
    public function placePayeeOrder(Order $order, int $gatewayId, int $payeeId, array $params = []): array;

    /**
     * @param User  $context
     * @param int   $gatewayId
     * @param array $requestPayloads
     * @return array|null
     */
    public function getPaymentValidationRules(User $context, int $gatewayId, array $requestPayloads = []): ?array;

    /**
     * @param User  $context
     * @param int   $gatewayId
     * @param array $requestPayloads
     * @param array $paymentParams
     * @return AbstractForm|null
     */
    public function getNextPaymentForm(User $context, int $gatewayId, array $requestPayloads, array $paymentParams = []): ?AbstractForm;
}
