<?php

namespace MetaFox\EMoney\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\EMoney\Contracts\SupportInterface;
use MetaFox\EMoney\Contracts\WithdrawMethodInterface;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Policies\WithdrawRequestPolicy;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\WithdrawServiceInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class Support implements SupportInterface
{
    public const SERVICE_VISA = 'visa';

    public const TEST_MODE = 'test';
    public const LIVE_MODE = 'live';

    public const TARGET_EXCHANGE_RATE_TYPE_AUTO    = 'auto';
    public const TARGET_EXCHANGE_RATE_TYPE_MANUAL  = 'manual';
    public const DEFAULT_TARGET_EXCHANGE_RATE_TYPE = self::TARGET_EXCHANGE_RATE_TYPE_AUTO;

    public const DEFAULT_TARGET_CURRENCY_CONVERSION_RATE = 'USD';

    public const MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER = 10;
    public const MINIMUM_EXCHANGE_RATE_NUMBER               = 0.0000000001;

    public const TRANSACTION_STATUS_APPROVED = 'approved';
    public const TRANSACTION_STATUS_PENDING  = 'pending';

    public const TRANSACTION_ACTOR_TYPE_USER   = 'user';
    public const TRANSACTION_ACTOR_TYPE_SYSTEM = 'system';

    public const TRANSACTION_INCOMING_AMOUNT_COLOR = '#47C366';
    public const TRANSACTION_OUTGOING_AMOUNT_COLOR = '#EE5A2B';

    public const TRANSACTION_PROCESSED_ICON = 'check-circle';
    public const TRANSACTION_PENDING_ICON   = 'sandclock-goingon-o';

    public const WITHDRAW_STATUS_WAITING_CONFIRMATION = 'waiting_confirmation';
    public const WITHDRAW_STATUS_PENDING              = 'pending';
    public const WITHDRAW_STATUS_PROCESSING           = 'processing';
    public const WITHDRAW_STATUS_PROCESSED            = 'processed';
    public const WITHDRAW_STATUS_CANCELLED            = 'cancelled';
    public const WITHDRAW_STATUS_DENIED               = 'denied';

    public const MINIMUM_WITHDRAW_AMOUNT = 0.01;

    public const WITHDRAW_REQUEST_REASON_TYPE_CANCEL = 'cancel';
    public const WITHDRAW_REQUEST_REASON_TYPE_DENY   = 'deny';

    /**
     * Received money when someone bought your listing.
     */
    public const INCOMING_TRANSACTION_TYPE_RECEIVED = 'received';
    public const INCOMING_TRANSACTION_TYPE_RECEIVED_FROM_ADMIN = 'received_from_admin';

    /**
     * Using for purchased like a Payment Gateway.
     */
    public const OUTGOING_TRANSACTION_TYPE_PURCHASED = 'purchased';
    public const OUTGOING_TRANSACTION_TYPE_WITHDRAWN = 'withdrawn';
    public const OUTGOING_TRANSACTION_TYPE_REDUCED_FROM_ADMIN = 'reduced_from_admin';

    public const TRANSACTION_SOURCE_INCOMING = 'incoming';
    public const TRANSACTION_SOURCE_OUTGOING = 'outgoing';
    public const AMOUNT_CURRENCY             = 'amount_%s';

    public const APPROVED_COLOR = '#47C366';
    public const PENDING_COLOR  = '#FFAB00';

    public const DEFAULT_MIN_ADJUST_BALANCE_VALUE = 0.01;
    public const DEFAULT_MAX_ADJUST_BALANCE_VALUE_PER_ADJUSTMENT = 999999999.99;

    public const DEFAULT_MAX_ADJUST_BALANCE_VALUE_IN_TOTAL = 999999999999.99;

    public const USER_BALANCE_ACTION_SEND = 'send';
    public const USER_BALANCE_ACTION_REDUCE = 'reduce';

    /**
     * Apply firstly for USD.
     *
     * @param string $currency
     *
     * @return float
     */
    public function getMinimumWithdrawalAmount(string $currency = self::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): float
    {
        $setting = Settings::get('ewallet.minimum_withdraw', []);
        $value   = Arr::get($setting, $currency, self::MINIMUM_WITHDRAW_AMOUNT);

        if ($value == 0) {
            $value = self::MINIMUM_WITHDRAW_AMOUNT;
        }

        return (float) $value;
    }

    public function getDefaultCurrency(): string
    {
        return self::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE;
    }

    public function getNotifiable(): ?User
    {
        return resolve(UserRepositoryInterface::class)->getSuperAdmin();
    }

    public function getNotifiables(): Collection
    {
        return resolve(UserRepositoryInterface::class)->getAllSuperAdmin();
    }

    public function getRequestStatusOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.pending'),
                'value' => self::WITHDRAW_STATUS_PENDING,
            ],
            [
                'label' => __p('ewallet::phrase.processing'),
                'value' => self::WITHDRAW_STATUS_PROCESSING,
            ],
            [
                'label' => __p('ewallet::phrase.completed'),
                'value' => self::WITHDRAW_STATUS_PROCESSED,
            ],
            [
                'label' => __p('ewallet::phrase.cancelled'),
                'value' => self::WITHDRAW_STATUS_CANCELLED,
            ],
            [
                'label' => __p('ewallet::phrase.denied'),
                'value' => self::WITHDRAW_STATUS_DENIED,
            ],
        ];
    }

    public function getTransactionStatusOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.pending'),
                'value' => self::TRANSACTION_STATUS_PENDING,
            ],
            [
                'label' => __p('ewallet::phrase.processed'),
                'value' => self::TRANSACTION_STATUS_APPROVED,
            ],
        ];
    }

    public function getTransactionStatusInfo(string $status): array
    {
        $infos = $this->transactionStatusInfos();

        return Arr::get($infos, $status);
    }

    protected function transactionStatusInfos(): array
    {
        return [
            self::TRANSACTION_STATUS_APPROVED => [
                'label' => __p('ewallet::phrase.processed'),
                'icon'  => self::TRANSACTION_PROCESSED_ICON,
                'color' => self::APPROVED_COLOR,
            ],
            self::TRANSACTION_STATUS_PENDING  => [
                'label' => __p('core::phrase.pending'),
                'icon'  => self::TRANSACTION_PENDING_ICON,
                'color' => self::PENDING_COLOR,
            ],
        ];
    }

    public function getTransactionBalanceInfo(Transaction $transaction): array
    {
        $color = $transaction->isIncoming()
            ? self::TRANSACTION_INCOMING_AMOUNT_COLOR
            : self::TRANSACTION_OUTGOING_AMOUNT_COLOR;

        $value = $this->getPriceFormat($transaction->balance_currency, $transaction->balance_price);

        return [
            'color'      => $color,
            'value'      => $value,
            'sign_value' => __p(
                'ewallet::phrase.format_price_with_sign',
                ['isPositive' => (int) $transaction->isIncoming(), 'price' => $value]
            ),
        ];
    }

    public function getBaseCurrencyOptions(?string $target = null): array
    {
        $currencies = app('currency')->getCurrencies();

        if ($target) {
            $currencies = array_filter($currencies, function ($currency) use ($target) {
                return $currency['code'] != $target;
            });
        }

        if (!count($currencies)) {
            return [];
        }

        return array_values(array_map(function ($currency) {
            return [
                'label' => $currency['code'],
                'value' => $currency['code'],
            ];
        }, $currencies));
    }

    public function getRequestStatuses(): array
    {
        return array_column($this->getRequestStatusOptions(), 'value');
    }

    public function isUsingNewAlias(): bool
    {
        if (!MetaFox::isMobile()) {
            return true;
        }

        if (version_compare(MetaFox::getApiVersion(), 'v1.6', '>=')) {
            return true;
        }

        return false;
    }

    public function getAppAlias(): string
    {
        if ($this->isUsingNewAlias()) {
            return 'ewallet';
        }

        return 'emoney';
    }

    public function getSourceOptions(): array
    {
        return [
            [
                'label' => __p('ewallet::phrase.incoming'),
                'value' => self::TRANSACTION_SOURCE_INCOMING,
            ],
            [
                'label' => __p('ewallet::phrase.outgoing'),
                'value' => self::TRANSACTION_SOURCE_OUTGOING,
            ],
        ];
    }

    public function getTypeOptions(): array
    {
        return [
            [
                'label' => __p('ewallet::phrase.bought_your_item'),
                'value' => self::INCOMING_TRANSACTION_TYPE_RECEIVED,
            ],
            [
                'label' => __p('ewallet::phrase.purchased_an_item'),
                'value' => self::OUTGOING_TRANSACTION_TYPE_PURCHASED,
            ],
            [
                'label' => __p('ewallet::phrase.withdrawn'),
                'value' => self::OUTGOING_TRANSACTION_TYPE_WITHDRAWN,
            ],
            [
                'label' => __p('ewallet::phrase.sent_to_your_wallet'),
                'value' => self::INCOMING_TRANSACTION_TYPE_RECEIVED_FROM_ADMIN,
            ],
            [
                'label' => __p('ewallet::phrase.reduced_from_your_wallet'),
                'value' => self::OUTGOING_TRANSACTION_TYPE_REDUCED_FROM_ADMIN,
            ],
        ];
    }

    public function getKeyPrice($currency): string
    {
        return sprintf(self::AMOUNT_CURRENCY, $currency);
    }

    protected function getWithdrawService(): WithdrawServiceInterface
    {
        return resolve(WithdrawServiceInterface::class);
    }

    public function getWithdrawalRequestParams(User $context): array
    {
        $currencies = [];

        $providers = [];

        $balances = resolve(StatisticRepositoryInterface::class)->getCurrencyOptions($context);

        $methods = $this->getWithdrawServiceProviderOptions();

        $cachedProviders = $this->getCachedWithdrawProviders($methods);

        $percentage = Settings::get('ewallet.withdraw_fee');

        foreach ($balances as $balance) {
            $currency = $balance['value'];

            if (!policy_check(WithdrawRequestPolicy::class, 'validateAmount', $context, $currency)) {
                continue;
            }

            $availableProviders = $this->getAvailableProviders($context, $currency, $methods, $cachedProviders);

            if (!count($availableProviders)) {
                continue;
            }

            if (is_numeric($percentage) && $percentage > 0) {
                $balance = array_merge($balance, [
                    'percentage_fee' => (float) $percentage,
                ]);
            }

            $minValue = $this->getMinimumWithdrawalAmount($currency);

            $currencies[] = array_merge($balance, [
                'min' => $minValue,
            ]);

            Arr::set($providers, $currency, $availableProviders);
        }

        return [
            'currencies' => $currencies,
            'providers'  => $providers,
        ];
    }

    protected function getWithdrawServiceProviderOptions(): array
    {
        return $this->getWithdrawService()->getActiveMethods()
            ->map(function ($method) {
                return [
                    'label' => $method->title,
                    'value' => $method->service,
                ];
            })
            ->toArray();
    }

    protected function getAvailableProviders(User $context, string $currency, array $providers, array $cachedProviderServices): array
    {
        $values = [];

        foreach ($providers as $provider) {
            /**
             * @var WithdrawMethodInterface $service
             */
            $service = Arr::get($cachedProviderServices, Arr::get($provider, 'value'));

            if (!$service->hasAccess($context, $currency)) {
                continue;
            }

            $values[] = $provider;
        }

        return $values;
    }

    protected function getCachedWithdrawProviders(array $methods): array
    {
        $cachedProviders = [];

        foreach ($methods as $method) {
            $value = Arr::get($method, 'value');

            Arr::set($cachedProviders, $value, $this->getWithdrawService()->getServiceProvider($value));
        }

        return $cachedProviders;
    }

    public function getPaymentBalanceCurrency(Order $order, array $extra = []): string
    {
        /*
         * Handle for deprecated data structure
         */
        if (Arr::has($extra, 'currency_payment')) {
            return Arr::get($extra, 'currency_payment');
        }

        if (Arr::has($extra, 'payment_gateway_balance_currency')) {
            return Arr::get($extra, 'payment_gateway_balance_currency');
        }

        return $order->currency;
    }

    protected function getPriceFormat(string $currency, float $price): ?string
    {
        return app('currency')->getPriceFormatByCurrencyId($currency, $price);
    }
}
