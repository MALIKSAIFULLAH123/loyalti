<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Support;

use ArrayObject;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Payment\Contracts\GatewayInterface;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;

/**
 * Class Notification.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
abstract class AbstractPaymentGateway implements GatewayInterface
{
    /** @var array<string, string> */
    protected array $billingFrequency = [];

    /**
     * @var array<string>
     */
    protected array $supportedCurrencies = [];

    protected Gateway $gateway;

    protected OrderRepositoryInterface $orderRepository;

    public function __construct(Gateway $gateway, OrderRepositoryInterface $orderRepository)
    {
        $this->setGateway($gateway);

        $this->orderRepository = $orderRepository;
    }

    protected function getReturnUrl(): string
    {
        return url_utility()->makeApiFullUrl('payment/return');
    }

    protected function getCancelUrl(): string
    {
        return url_utility()->makeApiFullUrl('payment/cancel');
    }

    public function setGateway(Gateway $gateway): GatewayInterface
    {
        $this->gateway = $gateway;

        return $this;
    }

    protected function getGateway(): Gateway
    {
        return $this->gateway;
    }

    /**
     * @inheritDoc
     */
    public function hasAccess(User $context, array $params): bool
    {
        $entityType = Arr::get($params, 'entity_type');

        /*
         * Migration for old definition
         */
        if (null === $entityType) {
            $entityType = Arr::get($params, 'entity');
        }

        $entityId = Arr::get($params, 'entity_id');

        if (!$this->hasAccessViaFilterMode($entityType)) {
            return false;
        }

        if (!$this->hasAccessViaItem($context, $entityType, $entityId)) {
            return false;
        }

        $payeeId = Arr::get($params, 'payee_id');

        if (null === $payeeId) {
            return true;
        }

        if (!$this->hasSystemExchangeRate($context, Arr::get($params, 'currency'))) {
            return false;
        }

        return true;
    }

    protected function hasSystemExchangeRate(User $context, ?string $currency = null): bool
    {
        $base = $currency;

        if (null === $base) {
            $base = app('currency')->getUserCurrencyId($context);
        }

        if (!$base) {
            return false;
        }

        $exchangeRate = app('events')->dispatch('ewallet.get_exchange_rate', [$base], true);

        if (null === $exchangeRate) {
            return false;
        }

        return true;
    }

    protected function hasAccessViaItem(User $context, ?string $entityType, ?int $entityId): bool
    {
        if (null === $entityType) {
            return false;
        }

        /*
         * If not define entityId, we will not verify permission
         */
        if (null === $entityId) {
            return true;
        }

        $gateway = $this->getGateway();

        $access = app('events')->dispatch('payment.gateway.has_access', [$context, $entityType, $entityId, $gateway], true);

        if (null === $access) {
            return true;
        }

        return (bool)$access;
    }

    public function hasAccessViaFilterMode(?string $entityType): bool
    {
        $gateway = $this->getGateway();

        $filterMode = $gateway->filter_mode;

        $filters = $gateway->filter_list;

        if (null === $filters) {
            return true;
        }

        if (null === $entityType) {
            return false;
        }

        return match ($filterMode) {
            'whitelist' => in_array($entityType, $filters, true),
            'blacklist' => !in_array($entityType, $filters, true),
            default     => false,
        };
    }

    /**
     * @inheritDoc
     */
    public function isDisabled(User $context, array $params): bool
    {
        return false;
    }

    public function title(): string
    {
        return $this->getGateway()->title;
    }

    public function describe(User $context, array $params): ?string
    {
        return '';
    }

    public function getFormApiUrl(): ?string
    {
        return null;
    }

    public function getFormFieldRules(): array
    {
        return [];
    }

    public function isSupportedCurrency(string $currencyCode): bool
    {
        if (empty($this->supportedCurrencies)) {
            return true;
        }

        return in_array($currencyCode, $this->supportedCurrencies);
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutButton(User $context, array $params): array
    {
        $gateway = $this->getGateway();

        $icon = $gateway->icon;

        if (is_string($icon) && MetaFox::isMobile()) {
            $icon = preg_replace('/^ico-/', '', $icon, 1);
        }

        $service = $gateway->getService();

        return [
            Builder::gatewayButton()
                ->name($gateway->service)
                ->variant($gateway->service)
                ->setAttributes([
                    'gateway_field_name' => 'payment_gateway',
                    'gateway_id'         => $gateway->entityId(),
                    'gateway_config'     => $this->getPublicConfigs($context, $params),
                    'item'               => Arr::get($params, 'item'),
                    'button_props'       => new ArrayObject($this->getGatewayButtonProps($context, $params)),
                    'icon'               => $icon,
                ])
                ->label($gateway->title)
                ->description($this->describe($context, $params))
                ->disabled($service->isDisabled($context, $params)),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPublicConfigs(User $context, array $params): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getGatewayButtonProps(User $context, array $params): array
    {
        return [];
    }

    public function getPaymentValidationRules(User $context, array $requestPayloads): ?array
    {
        return null;
    }

    public function hasBuyerConfigurationAccess(): bool
    {
        return false;
    }

    /**
     * @param User  $context
     * @param array $requestPayloads
     * @param array $paymentParams
     * @return AbstractForm|null
     */
    public function getNextPaymentForm(User $context, array $requestPayloads, array $paymentParams = []): ?AbstractForm
    {
        return null;
    }

    /**
     * @param User  $context
     * @param array $requestPayloads
     * @return array
     */
    public function getExtraPaymentParams(User $context, array $requestPayloads = []): array
    {
        return [];
    }

    public function getReturnUrlOnSuccessfulPayment(?string $currentUrl = null): ?string
    {
        if (is_string($currentUrl)) {
            return $currentUrl;
        }

        return $this->getReturnUrl();
    }

    public function getReturnUrlOnCancelledPayment(?string $currentUrl = null): ?string
    {
        if (is_string($currentUrl)) {
            return $currentUrl;
        }

        return $this->getCancelUrl();
    }

    protected function resolveReturnUrl(string $returnUrl): string
    {
        $returnUrl = trim($returnUrl);

        if (false !== filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            return $returnUrl;
        }

        return url_utility()->makeApiFullUrl($returnUrl);
    }

    protected function getReturnUrlConfig(string $name): ?string
    {
        if (!is_array($this->gateway->config)) {
            return null;
        }

        $returnUrl = Arr::get($this->gateway->config, $name);

        if (!is_string($returnUrl) || trim($returnUrl) === '') {
            return null;
        }

        return $returnUrl;
    }
}
