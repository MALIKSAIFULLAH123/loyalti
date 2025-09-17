<?php

namespace MetaFox\EMoney\Contracts;

use Illuminate\Support\Collection;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Support\Support;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\User;

interface SupportInterface
{
    /**
     * @param string $currency
     *
     * @return float
     */
    public function getMinimumWithdrawalAmount(string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): float;

    /**
     * @return string
     */
    public function getDefaultCurrency(): string;

    /**
     * @return User|null
     */
    public function getNotifiable(): ?User;

    /**
     * @return Collection
     */
    public function getNotifiables(): Collection;

    /**
     * @return array
     */
    public function getRequestStatusOptions(): array;

    /**
     * @return array
     */
    public function getRequestStatuses(): array;

    /**
     * @param string|null $target
     *
     * @return array
     */
    public function getBaseCurrencyOptions(?string $target = null): array;

    /**
     * @return array
     */
    public function getTransactionStatusOptions(): array;

    /**
     * @param string $status
     * @return array
     */
    public function getTransactionStatusInfo(string $status): array;

    /**
     * @param Transaction $transaction
     * @return array
     */
    public function getTransactionBalanceInfo(Transaction $transaction): array;

    /**
     * @return bool
     */
    public function isUsingNewAlias(): bool;

    /**
     * @return string
     */
    public function getAppAlias(): string;

    /**
     * @return array
     */
    public function getSourceOptions(): array;

    /**
     * @return array
     */
    public function getTypeOptions(): array;

    /**
     * @param $currency
     *
     * @return string
     */
    public function getKeyPrice($currency): string;

    /**
     * @param User $context
     * @return array
     */
    public function getWithdrawalRequestParams(User $context): array;

    /**
     * @param Order $order
     * @param array $extra
     * @return string
     */
    public function getPaymentBalanceCurrency(Order $order, array $extra = []): string;
}
