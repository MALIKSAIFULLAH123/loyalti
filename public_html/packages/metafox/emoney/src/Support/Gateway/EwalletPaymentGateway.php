<?php

namespace MetaFox\EMoney\Support\Gateway;

use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Facades\Payment as EwalletPayment;
use MetaFox\EMoney\Http\Resources\v1\Gateway\SelectCurrencyForm;
use MetaFox\EMoney\Http\Resources\v1\Gateway\SelectCurrencyMobileForm;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Constants;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\AbstractPaymentGateway;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Rules\AllowInRule;
use RuntimeException;

class EwalletPaymentGateway extends AbstractPaymentGateway
{
    public const GATEWAY_SERVICE_NAME = 'ewallet';

    public static function getGatewayServiceName(): string
    {
        return self::GATEWAY_SERVICE_NAME;
    }

    public function createGatewayOrder(Order $order, array $params = []): array
    {
        $data = $order->toGatewayOrder();

        if (!$data) {
            throw new RuntimeException('Invalid order.');
        }

        $result = EwalletPayment::processPayment($order, $params);

        $valid = is_array($result) && Arr::has($result, 'gateway_order_id');

        $redirectUrl = Arr::get($params, 'return_url');

        if (!$valid) {
            $redirectUrl = Arr::get($params, 'cancel_url');
        }

        $data = [
            'status'               => $valid,
            'gateway_redirect_url' => $this->toRedirectUrl($redirectUrl),
        ];

        if (!is_array($result)) {
            return $data;
        }

        // Return data based on result of previous step
        return array_merge($data, $result);
    }

    protected function toRedirectUrl(?string $url = null): ?string
    {
        if (null === $url) {
            return null;
        }

        if (MetaFox::isMobile()) {
            return url_utility()->convertUrlToLink($url, true);
        }

        return $url;
    }

    public function getGatewayTransaction(string $gatewayTransactionId): ?array
    {
        return null;
    }

    public function getGatewayOrder(string $gatewayOrderId): ?array
    {
        return null;
    }

    public function hasAccess(User $context, array $params): bool
    {
        if (!parent::hasAccess($context, $params)) {
            return false;
        }

        $price = Arr::get($params, 'price');

        if (!is_numeric($price)) {
            return false;
        }

        $price = (float) $price;

        if ($price <= 0) {
            return false;
        }

        $balances = resolve(StatisticRepositoryInterface::class)->getUserBalancesOptions($context);

        return count($balances) > 0;
    }

    /**
     * @inheritDoc
     */
    public function isDisabled(User $context, array $params): bool
    {
        $currency = app('currency')->getUserCurrencyId($context);
        $balances = resolve(StatisticRepositoryInterface::class)->getUserBalancesOptions($context);

        if (empty($balances)) {
            return true;
        }

        $price = Arr::get($params, 'price');

        if (!is_numeric($price)) {
            return false;
        }

        $price = (float) $price;

        foreach ($balances as $balance) {
            if ($balance['value'] != $currency) {
                $price = app('ewallet.conversion-rate')->getConversedAmount($currency, $price, $balance['value']);
            }

            if ($balance['total_balance'] >= $price) {
                return false;
            }
        }

        return true;
    }

    public function describe(User $context, array $params): ?string
    {
        return $this->isDisabled($context, $params)
            ? __p('ewallet::phrase.the_balance_in_the_wallet_is_insufficient_for_the_payment', [
                'gateway' => __p('ewallet::phrase.app_name'),
            ])
            : '';
    }

    public function getPaymentValidationRules(User $context, array $requestPayloads): ?array
    {
        $currencies = app('currency')->getActiveOptions();

        return [
            'payment_gateway_balance_currency' => ['sometimes', 'string', new AllowInRule(array_column($currencies, 'value'))],
        ];
    }

    public function getNextPaymentForm(User $context, array $requestPayloads, array $paymentParams = []): ?AbstractForm
    {
        if (Arr::has($requestPayloads, 'payment_gateway_balance_currency')) {
            return null;
        }

        $form = match (MetaFox::isMobile()) {
            false => new SelectCurrencyForm(),
            true  => new SelectCurrencyMobileForm()
        };

        $previousProcessChildId = Arr::get($paymentParams, 'previous_process_child_id');
        $setFormName            = Arr::get($paymentParams, 'form_name');
        $price                  = Arr::get($paymentParams, 'price');
        $currencyId             = Arr::get($paymentParams, 'currency_id');
        $method                 = Arr::get($paymentParams, 'method',Constants::METHOD_POST);
        $actionUrl              = Arr::get($paymentParams, 'action_url');

        $form->setValues($requestPayloads);
        $form->setActionUrl($actionUrl);
        $form->setPreviousProcessChildId($previousProcessChildId);
        $form->setFormName($setFormName);
        $form->setPrice($price);
        $form->setCurrencyId($currencyId);
        $form->setMethod($method);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot']);
        }

        return $form;
    }

    public function getExtraPaymentParams(User $context, array $requestPayloads = []): array
    {
        return [
            'payment_gateway_balance_currency' => Arr::get($requestPayloads, 'payment_gateway_balance_currency'),
        ];
    }
}
