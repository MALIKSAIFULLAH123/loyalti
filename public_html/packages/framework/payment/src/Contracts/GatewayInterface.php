<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Contracts;

use MetaFox\Form\AbstractForm;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Interface GatewayInterface.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface GatewayInterface
{
    /**
     * getGatewayServiceName.
     * @return string
     */
    public static function getGatewayServiceName(): string;

    /**
     * Set gateway config.
     * @param  Gateway $gateway
     * @return self
     */
    public function setGateway(Gateway $gateway): self;

    /**
     * createGatewayOrder.
     *
     * @param  Order                $order
     * @param  array<string, mixed> $params additional parameters
     * @return array<string, mixed>
     */
    public function createGatewayOrder(Order $order, array $params = []): array;

    /**
     * getGatewayTransaction.
     *
     * @param  string        $gatewayTransactionId
     * @return ?array<mixed>
     */
    public function getGatewayTransaction(string $gatewayTransactionId): ?array;

    /**
     * getGatewayOrder.
     *
     * @param  string        $gatewayOrderId
     * @return ?array<mixed>
     */
    public function getGatewayOrder(string $gatewayOrderId): ?array;

    /**
     * @param  User                 $context
     * @param  array<string ,mixed> $params
     * @return bool
     */
    public function hasAccess(User $context, array $params): bool;

    /**
     * @param  string|null $entityType
     * @return bool
     */
    public function hasAccessViaFilterMode(?string $entityType): bool;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $params
     * @return bool
     */
    public function isDisabled(User $context, array $params): bool;

    /**
     * @return string
     */
    public function title(): string;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $params
     * @return string|null
     */
    public function describe(User $context, array $params): ?string;

    /**
     * @return string|null
     */
    public function getFormApiUrl(): ?string;

    /**
     * @return array
     */
    public function getFormFieldRules(): array;

    /**
     * isSupportedCurrency.
     *
     * @param  string $currencyCode
     * @return bool
     */
    public function isSupportedCurrency(string $currencyCode): bool;

    /**
     * @param  User  $context
     * @param  array $params
     * @return array
     */
    public function getCheckoutButton(User $context, array $params): array;

    /**
     * @param  User  $context
     * @param  array $params
     * @return array
     */
    public function getPublicConfigs(User $context, array $params): array;

    /**
     * @param  User  $context
     * @param  array $params
     * @return array
     */
    public function getGatewayButtonProps(User $context, array $params): array;

    /**
     * @param User  $context
     * @param array $requestPayloads
     * @return array|null
     */
    public function getPaymentValidationRules(User $context, array $requestPayloads): ?array;

    /**
     * @return bool
     */
    public function hasBuyerConfigurationAccess(): bool;

    /**
     * @param User  $context
     * @param array $requestPayloads
     * @param array $paymentParams
     * @return AbstractForm|null
     */
    public function getNextPaymentForm(User $context, array $requestPayloads, array $paymentParams = []): ?AbstractForm;

    /**
     * @param User  $context
     * @param array $requestPayloads
     * @return array
     */
    public function getExtraPaymentParams(User $context, array $requestPayloads = []): array;

    /**
     * @param string|null $currentUrl
     * @return string|null
     */
    public function getReturnUrlOnSuccessfulPayment(?string $currentUrl = null): ?string;

    /**
     * @param string|null $currentUrl
     * @return string|null
     */
    public function getReturnUrlOnCancelledPayment(?string $currentUrl = null): ?string;
}
