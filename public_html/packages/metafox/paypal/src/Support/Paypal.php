<?php

namespace MetaFox\Paypal\Support;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MetaFox\Payment\Contracts\HasSupportSubscription;
use MetaFox\Payment\Contracts\HasSupportWebhook;
use MetaFox\Payment\Contracts\PayeePaymentInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\AbstractPaymentGateway;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Payment\Support\Payment as SupportPayment;
use MetaFox\Payment\Support\Traits\HasSupportSubscriptionTrait;
use MetaFox\Paypal\Support\Traits\OrderWebhookTrait;
use MetaFox\Paypal\Support\Traits\PaymentWebhookTrait;
use MetaFox\Paypal\Support\Traits\SubscriptionWebhookTrait;
use MetaFox\Platform\Contracts\User;
use RuntimeException;
use Srmklive\PayPal\Services\PayPal as ServicesPayPal;

/**
 * Class Paypal.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class Paypal extends AbstractPaymentGateway implements HasSupportSubscription, HasSupportWebhook, PayeePaymentInterface
{
    use HasSupportSubscriptionTrait;
    use OrderWebhookTrait;
    use PaymentWebhookTrait;
    use SubscriptionWebhookTrait;

    private mixed $provider;

    protected array $billingFrequency = [
        SupportPayment::BILLING_DAILY    => 'DAY',
        SupportPayment::BILLING_WEEKLY   => 'WEEK',
        SupportPayment::BILLING_MONTHLY  => 'MONTH',
        SupportPayment::BILLING_ANNUALLY => 'YEAR',
    ];

    protected array $supportedCurrencies = [
        'AUD',
        'BRL',
        'CAD',
        // 'CNY', // In-country PayPal accounts only
        'CZK',
        'DKK',
        'EUR',
        'HKD',
        // 'HUF', // This currency does not support decimals
        'ILS',
        // 'JPY', // This currency does not support decimals
        // 'MYR', // In-country PayPal accounts only
        'MXN',
        // 'TWD', // This currency does not support decimals
        'NZD',
        'NOK',
        'PHP',
        'PLN',
        'GBP',
        'RUB',
        'SGD',
        'SEK',
        'CHF',
        'THB',
        'USD',
    ];

    public static function getGatewayServiceName(): string
    {
        return 'paypal';
    }

    /**
     * Get the service provider.
     */
    private function getProvider(): ServicesPayPal
    {
        if (!empty($this->provider)) {
            return $this->provider;
        }

        $gateway = $this->gateway;

        $mode          = $gateway->is_test ? 'sandbox' : 'live';
        $gatewayConfig = $gateway->config;
        $webhookId     = Arr::get($gatewayConfig, 'webhook_id', '');
        $config        = [
            'app_id'         => '', //dummy
            'mode'           => $mode,
            $mode            => $gatewayConfig,
            'payment_action' => 'Sale',
            'locale'         => 'en_US',
            'notify_url'     => $this->getWebhookUrl(),
            'currency'       => 'USD',
            'validate_ssl'   => !app()->isLocal(),
        ];

        $provider       = new ServicesPayPal($config);
        $provider->setWebHookID($webhookId);
        $this->provider = $provider;

        return $this->provider;
    }

    public function createGatewaySubscription(Order $order, array $params = []): array
    {
        $data = $order->toGatewayOrder();
        if (!$order->isRecurringOrder() || !$data) {
            throw new RuntimeException('Invalid recurring order.');
        }

        $billingAmount    = Arr::get($data, 'billing_amount');
        $billingFrequency = $this->getSupportedBillingFrequency(Arr::get($data, 'billing_frequency'));
        $billingInterval  = Arr::get($data, 'billing_interval');
        $trialAmount      = Arr::get($data, 'trial_amount');
        $trialFrequency   = $this->getSupportedBillingFrequency(Arr::get(
            $data,
            'trial_frequency',
            SupportPayment::BILLING_MONTHLY
        ));
        $trialInterval = Arr::get($data, 'trial_interval');
        $userTitle     = Arr::get($data, 'user_title');
        $email         = Arr::get($data, 'email');

        $service = $this->getProvider();
        $service->getAccessToken();
        $service->setCurrency($order->currency)
            ->addProduct($order->title, $order->title, 'SERVICE', 'SOFTWARE'); // TODO: improve product and plan title/subscription;

        if (isset($trialAmount) && isset($trialInterval)) {
            $service->addPlanTrialPricing($trialFrequency, $trialInterval, $trialAmount);
        }

        $service->addCustomPlan('Subscription', Arr::get($params, 'description', 'Subscription'), $billingAmount, $billingFrequency, $billingInterval)
            ->setReturnAndCancelUrl(
                $this->getReturnUrlOnSuccessfulPayment(Arr::get($params, 'return_url')),
                $this->getReturnUrlOnCancelledPayment(Arr::get($params, 'cancel_url')),
            );

        $result = $service->setupSubscription($userTitle, $email, Carbon::now()->addMinute());

        if (!is_array($result) || !Arr::has($result, 'id')) {
            throw new RuntimeException('Could not initialize gateway subscription.');
        }

        /** @var array<mixed> $links */
        $links       = Arr::get($result, 'links', []);
        $approvalUrl = collect($links)
            ->where('rel', 'approve')
            ->pluck(['href'])
            ->first();

        return [
            'status'                  => true,
            'gateway_subscription_id' => Arr::get($result, 'id'),
            'gateway_redirect_url'    => $approvalUrl ?? null,
        ];
    }

    public function createGatewayOrder(Order $order, array $params = []): array
    {
        return $this->processOnetimePayment($order, $params);
    }

    protected function throwPayeeException(): void
    {
        throw new RuntimeException('Invalid payee.');
    }

    /**
     * @param  int        $payeeId
     * @return array|null
     * @throw RuntimeException
     */
    protected function getPayeeInformation(int $payeeId): ?array
    {
        if (!$payeeId) {
            $this->throwPayeeException();
        }

        $rules = $this->getFormFieldRules();

        $configuration = app('events')->dispatch('payment.user.configuration', [$payeeId, 'paypal'], true);

        if (null === $configuration) {
            $this->throwPayeeException();
        }

        $validator = Validator::make($configuration, $rules);

        if (!$validator->passes()) {
            $this->throwPayeeException();
        }

        return $validator->validated();
    }

    public function cancelGatewaySubscription(Order $order): array
    {
        if (empty($order->gateway_subscription_id)) {
            throw new RuntimeException('The referenced subscription id does not exist.');
        }

        $service = $this->getProvider();
        $service->getAccessToken();
        $service->cancelSubscription($order->gateway_subscription_id, __p('payment::phrase.user_cancelled_manually'));

        return [
            'status' => true,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getGatewaySubscription(string $gatewaySubscriptionId): ?array
    {
        $service = $this->getProvider();
        $service->getAccessToken();
        $gatewaySubscription = $service->showSubscriptionDetails($gatewaySubscriptionId);

        if (!is_array($gatewaySubscription) || !Arr::has($gatewaySubscription, 'id')) {
            return null;
        }

        return $gatewaySubscription;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getGatewayTransaction(string $gatewayTransactionId): ?array
    {
        // to be implemented later
        return [];
    }

    public function getGatewayOrder(string $gatewayOrderId): ?array
    {
        // to be implemented later
        $service = $this->getProvider();
        $service->getAccessToken();
        $gatewayOrder = $service->showOrderDetails($gatewayOrderId);
        if (!is_array($gatewayOrder) || !Arr::has($gatewayOrder, 'id')) {
            return null;
        }

        return $gatewayOrder;
    }

    public function getWebhookUrl(): string
    {
        return url_utility()->makeApiFullUrl('api/v1/paypal/notify');
    }

    public function verifyWebhook(array $payload): bool
    {
        if (app()->isLocal()) {
            return true;
        }

        $request = Arr::get($payload, 'request');
        if (!$request instanceof Request) {
            return false;
        }

        $eventId  = $request->get('id');
        $provider = $this->getProvider();
        $provider->getAccessToken();
        $result = $provider->verifyIPN($request);

        Log::channel('payment')->info("Webhook verification result: {$eventId}", $result);

        if (!is_array($result) || Arr::get($result, 'verification_status') != 'SUCCESS') {
            return false;
        }

        return true;
    }

    public function handleWebhook(array $payload): bool
    {
        $request = Arr::get($payload, 'request');
        if (!$this->verifyWebhook($payload) || !$request instanceof Request) {
            throw new RuntimeException('Invalid request');
        }

        $body         = $request->all();
        $eventType    = Str::lower(Arr::get($body, 'event_type'));
        $handleMethod = Str::camel(Str::replace('.', ' ', 'handle.' . $eventType));
        if (method_exists($this, $handleMethod)) {
            try {
                Payment::onWebhook($body);

                $this->$handleMethod($body);

                return true;
            } catch (Exception $e) {
                Log::channel('payment')->error("Exception when handling webhook: {$e->getMessage()}", $body);
            }
        }

        return false;
    }

    public function getFormApiUrl(): ?string
    {
        return url_utility()->makeApiUrl('payment-gateway/configuration-form/paypal.gateway.user-form/:id');
    }

    public function getFormFieldRules(): array
    {
        return [
            'merchant_id' => ['required', 'string'],
        ];
    }

    public function hasAccess(User $context, array $params): bool
    {
        if (!parent::hasAccess($context, $params)) {
            return false;
        }

        if (!app_active('metafox/paypal')) {
            return false;
        }

        if (!$this->isConfigurable()) {
            return false;
        }

        return true;
    }

    private function processOnetimePayment(Order $order, array $params, ?User $payee = null): array
    {
        $data = $order->toGatewayOrder();

        if (!$data) {
            throw new RuntimeException('Invalid order.');
        }

        $service = $this->getProvider();

        $service->getAccessToken();

        $purchaseUnit = [
            'amount' => [
                'currency_code' => $order->currency,
                'value'         => $order->total,
            ],
        ];

        if ($payee instanceof User) {
            $purchaseUnit['payee'] = $this->getPayeeInformation($payee->entityId());
        }

        if (Arr::has($params, 'description')) {
            Arr::set($purchaseUnit, 'description', Arr::get($params, 'description'));
        }

        $result = $service->createOrder([
            'intent'              => 'CAPTURE',
            'purchase_units'      => [$purchaseUnit],
            'application_context' => [
                'return_url' => $this->getReturnUrlOnSuccessfulPayment(Arr::get($params, 'return_url')),
                'cancel_url' => $this->getReturnUrlOnCancelledPayment(Arr::get($params, 'cancel_url')),
            ],
        ]);

        if (!is_array($result) || Arr::get($result, 'status') != 'CREATED') {
            Log::channel('payment')->error('Error when initializing gateway order.', [$purchaseUnit, $result]);

            throw new RuntimeException('Could not initialize gateway order.');
        }

        /** @var array<mixed> $links */
        $links       = Arr::get($result, 'links', []);

        $approvalUrl = collect($links)
            ->where('rel', 'approve')
            ->pluck(['href'])
            ->first();

        return [
            'status'               => true,
            'gateway_order_id'     => Arr::get($result, 'id'),
            'gateway_redirect_url' => $approvalUrl ?? null,
        ];
    }

    public function createPayeeOrder(Order $order, User $payee, array $params = []): array
    {
        return $this->processOnetimePayment($order, $params, $payee);
    }

    private function isConfigurable(): bool
    {
        $gatewayConfig = $this->gateway->config;

        if (!is_array($gatewayConfig)) {
            return false;
        }

        if (!Arr::get($gatewayConfig, 'client_id')) {
            return false;
        }

        if (!Arr::get($gatewayConfig, 'client_secret')) {
            return false;
        }

        if (!Arr::get($gatewayConfig, 'webhook_id')) {
            return false;
        }

        return true;
    }

    public function getReturnUrlOnSuccessfulPayment(?string $currentUrl = null): ?string
    {
        $returnUrl = $this->getReturnUrlConfig('return_url_on_success');

        if (null === $returnUrl) {
            return parent::getReturnUrlOnSuccessfulPayment($currentUrl);
        }

        return $this->resolveReturnUrl($returnUrl);
    }

    public function getReturnUrlOnCancelledPayment(?string $currentUrl = null): ?string
    {
        $returnUrl = $this->getReturnUrlConfig('return_url_on_cancelled');

        if (null === $returnUrl) {
            return parent::getReturnUrlOnCancelledPayment($currentUrl);
        }

        return $this->resolveReturnUrl($returnUrl);
    }
}
