<?php

namespace MetaFox\Payment\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Payment\Contracts\GatewayManagerInterface;
use MetaFox\Payment\Contracts\HasSupportSubscription;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Contracts\PayeePaymentInterface;
use MetaFox\Payment\Contracts\ServiceInterface;
use MetaFox\Payment\Exceptions\UnsupportedCurrencyException;
use MetaFox\Payment\Http\Resources\v1\Gateway\Admin\GatewayForm;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Repositories\TransactionRepositoryInterface;
use MetaFox\Payment\Support\Traits\PaymentServiceTrait;
use MetaFox\Payment\Support\Traits\SubscriptionServiceTrait;
use MetaFox\User\Support\Facades\UserEntity;
use RuntimeException;
use MetaFox\Platform\Contracts\User;

/**
 * Class Payment.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Payment implements ServiceInterface
{
    use PaymentServiceTrait;
    use SubscriptionServiceTrait;

    public const PAYMENT_ONETIME   = 'onetime';
    public const PAYMENT_RECURRING = 'recurring';

    public const BILLING_DAILY    = 'day';
    public const BILLING_WEEKLY   = 'week';
    public const BILLING_MONTHLY  = 'month';
    public const BILLING_ANNUALLY = 'year';

    private DriverRepositoryInterface      $driverRepository;
    private GatewayManagerInterface        $gatewayManager;
    private OrderRepositoryInterface       $orderRepository;
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(
        DriverRepositoryInterface      $driverRepository,
        OrderRepositoryInterface       $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        GatewayManagerInterface        $gatewayManager,
    ) {
        $this->driverRepository      = $driverRepository;
        $this->orderRepository       = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->gatewayManager        = $gatewayManager;
    }

    public function getManager(): GatewayManagerInterface
    {
        return $this->gatewayManager;
    }

    public function getGatewayAdminFormById(int $gatewayId): ?GatewayForm
    {
        $gateway = $this->gatewayManager->getGatewayById($gatewayId);
        if (!$gateway instanceof Gateway) {
            return null;
        }

        $form = $this->getGatewayAdminFormByName("{$gateway->service}.gateway.form");

        if (!$form instanceof GatewayForm) {
            // default admin gateway form
            $form = $this->getGatewayAdminFormByName('payment.gateway.form');
        }

        $form?->boot($gatewayId);

        return $form;
    }

    public function getGatewayAdminFormByName(string $formName): ?GatewayForm
    {
        $driver = $this->driverRepository
            ->getDriver(Constants::DRIVER_TYPE_FORM, $formName, 'admin');

        /** @var ?GatewayForm $form */
        $form = resolve($driver);

        return $form;
    }

    public function initOrder(IsBillable $billable): Order
    {
        app('events')->dispatch('payment.order_initializing', [$billable]);

        $order = $this->orderRepository->createOrder($billable);

        app('events')->dispatch('payment.order_initialized', [$billable, $order]);

        $order->refresh();

        return $order;
    }

    protected function updatePayee(Order $order, ?int $payeeId): void
    {
        if (null === $payeeId) {
            return;
        }

        $exchangeRate = app('events')->dispatch('ewallet.get_exchange_rate', [$order->currency], true);

        if (null === $exchangeRate) {
            throw new AuthorizationException(__p('payment::validation.no_exchange_rate_available_for_currency', ['currency' => $order->currency]));
        }

        $payee = UserEntity::getById($payeeId)->detail;

        if (null === $payee) {
            throw new AuthorizationException(__p('payment::validation.payee_does_not_exist'));
        }

        $order->update([
            'payee_id'   => $payee->entityId(),
            'payee_type' => $payee->entityType(),
        ]);
    }

    public function placePayeeOrder(Order $order, int $gatewayId, int $payeeId, array $params = []): array
    {
        $this->validatePlaceOrder($order, $gatewayId);

        $payee = UserEntity::getById($payeeId)->detail;

        if (null === $payee) {
            throw new AuthorizationException(__p('payment::validation.payee_does_not_exist'));
        }

        app('events')->dispatch('payment.place_order_processing', [$order, $params]);

        if ($order->isRecurringOrder()) {
            throw new RuntimeException('Invalid onetime order.');
        }

        $service = $this->gatewayManager->getGatewayServiceById($gatewayId);

        if (!$service instanceof PayeePaymentInterface) {
            throw new RuntimeException('Gateway does not support this feature.');
        }

        $result = $service->createPayeeOrder($order, $payee, $params);

        if (!$result['status']) {
            throw new RuntimeException('Could not create gateway order.');
        }

        $order->gateway_id       = $gatewayId;
        $order->gateway_order_id = $result['gateway_order_id'];
        $order->status           = Order::STATUS_PENDING_APPROVAL;
        $order->recurring_status = Order::RECURRING_STATUS_UNSET;
        $order->save();

        app('events')->dispatch('payment.place_order_processed', [$order, $result]);

        return $result;
    }

    private function validatePlaceOrder(Order $order, int $gatewayId): void
    {
        if (!$order->isStatusInitialized()) {
            throw new RuntimeException('The order has been already processed.');
        }

        $service = $this->gatewayManager->getActiveGatewayServiceById($gatewayId);

        if (!$service->isSupportedCurrency($order->currency)) {
            throw new UnsupportedCurrencyException(__p('payment::phrase.unsupported_currency', [
                'currency_code' => $order->currency,
                'gateway'       => $service->title(),
            ]));
        }
    }

    /**
     * @param Order $order
     * @param int   $gatewayId
     * @param array $params
     *
     * @return mixed[]
     * @throws AuthorizationException
     * @throws UnsupportedCurrencyException
     */
    public function placeOrder(Order $order, int $gatewayId, array $params = []): array
    {
        $this->validatePlaceOrder($order, $gatewayId);

        $this->updatePayee($order, Arr::get($params, 'payee_id'));

        return match ($order->isRecurringOrder()) {
            true    => $this->placeRecurringOrder($order, $gatewayId, $params),
            default => $this->placeOnetimeOrder($order, $gatewayId, $params),
        };
    }

    /**
     * placeRecurringOrder.
     *
     * @param Order        $order
     * @param int          $gatewayId
     * @param array<mixed> $params
     *
     * @return array<mixed>
     * @throws RuntimeException
     */
    private function placeRecurringOrder(Order $order, int $gatewayId, array $params = []): array
    {
        app('events')->dispatch('payment.place_order_processing', [$order, $params]);
        app('events')->dispatch('payment.place_subscription_processing', [$order, $params]);

        if (!$order->isRecurringOrder()) {
            throw new RuntimeException('Invalid recurring order.');
        }

        $service = $this->gatewayManager->getGatewayServiceById($gatewayId);
        if (!$service instanceof HasSupportSubscription) {
            throw new RuntimeException('Gateway does not support subscription payment');
        }

        $result = $service->createGatewaySubscription($order, $params);

        if (!Arr::get($result, 'gateway_subscription_id')) {
            $message = Arr::get($result, 'non_support_message', 'Could not create subscription.');

            throw new RuntimeException($message);
        }

        $order->gateway_id              = $gatewayId;
        $order->gateway_subscription_id = $result['gateway_subscription_id'];
        $order->status                  = Order::STATUS_PENDING_APPROVAL;
        $order->recurring_status        = Order::RECURRING_STATUS_PENDING;
        $order->save();

        app('events')->dispatch('payment.place_order_processed', [$order, $result]);
        app('events')->dispatch('payment.place_subscription_processed', [$order, $result]);

        return $result;
    }

    /**
     * placeOnetimeOrder.
     *
     * @param Order        $order
     * @param int          $gatewayId
     * @param array<mixed> $params
     *
     * @return array<mixed>
     * @throws RuntimeException
     */
    private function placeOnetimeOrder(Order $order, int $gatewayId, array $params = [])
    {
        app('events')->dispatch('payment.place_order_processing', [$order, $params]);

        if ($order->isRecurringOrder()) {
            throw new RuntimeException('Invalid onetime order.');
        }

        $service = $this->gatewayManager->getGatewayServiceById($gatewayId);

        $result = $service->createGatewayOrder($order, $params);

        if (!$result['status']) {
            throw new RuntimeException('Could not create gateway order.');
        }

        $order->gateway_id       = $gatewayId;
        $order->gateway_order_id = $result['gateway_order_id'];
        $order->status           = Order::STATUS_PENDING_APPROVAL;
        $order->recurring_status = Order::RECURRING_STATUS_UNSET;
        $order->save();

        if (!empty($params)){
            $result = array_merge($result,$params);
        }

        app('events')->dispatch('payment.place_order_processed', [$order, $result]);

        return $result;
    }

    public function onWebhook(array $payload = []): void
    {
        Log::channel('payment')->info('Webhook received: ', $payload);
    }

    public function getPaymentValidationRules(User $context, int $gatewayId, array $requestPayloads = []): ?array
    {
        $service = $this->gatewayManager->getGatewayServiceById($gatewayId);

        return $service->getPaymentValidationRules($context, $requestPayloads);
    }

    public function getNextPaymentForm(User $context, int $gatewayId, array $requestPayloads, array $paymentParams = []): ?AbstractForm
    {
        $service = $this->gatewayManager->getGatewayServiceById($gatewayId);

        return $service->getNextPaymentForm($context, $requestPayloads, $paymentParams);
    }
}
