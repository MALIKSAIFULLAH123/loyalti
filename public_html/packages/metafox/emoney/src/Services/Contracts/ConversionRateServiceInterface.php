<?php

namespace MetaFox\EMoney\Services\Contracts;

use Illuminate\Support\Collection;
use MetaFox\EMoney\Models\ConversionRate;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\User;

interface ConversionRateServiceInterface
{
    /**
     * @param  string     $target
     * @return Collection
     */
    public function viewConversionRates(string $target = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): Collection;

    /**
     * @param  string     $target
     * @param  array|null $baseCodes
     * @return void
     */
    public function findMissing(string $target, ?array $baseCodes = null): void;

    /**
     * @param  string     $base
     * @param  string     $target
     * @param  string     $type
     * @param  float|null $exchangeRate
     * @param  int|null   $logId
     * @return array
     */
    public function prepareData(string $base, string $target, string $type = Support::DEFAULT_TARGET_EXCHANGE_RATE_TYPE, ?float $exchangeRate = null, ?int $logId = null): array;

    /**
     * @param  string     $base
     * @param  string     $target
     * @return float|null
     */
    public function getExchangeRate(string $base, string $target): ?float;

    /**
     * @param  string     $baseCurrency
     * @param  string     $targetCurrency
     * @return float|null
     */
    public function getExchangeRateWithInverse(string $baseCurrency, string $targetCurrency): ?float;

    /**
     * @param  string $target
     * @return array
     */
    public function getBaseCurrencyCodesByTarget(string $target): array;

    /**
     * @param  string $base
     * @return array
     */
    public function getBaseSettings(string $base): array;

    /**
     * @param  string $target
     * @return array
     */
    public function getTargetSettings(string $target): array;

    /**
     * @return array
     */
    public function getSettings(): array;

    /**
     * @param  int            $id
     * @param  array          $attributes
     * @return ConversionRate
     */
    public function updateSetting(int $id, array $attributes): ConversionRate;

    /**
     * @param  string $target
     * @param  array  $settings
     * @return void
     */
    public function updateTargetSettings(string $target, array $settings): void;

    /**
     * @param  float      $price
     * @param  string     $base
     * @param  string     $target
     * @return array|null
     */
    public function getBalancePrice(float $price, string $base, string $target = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): ?array;

    /**
     * @param  float      $total
     * @param  float|null $percentage
     * @return float
     */
    public function getCommissionFee(float $total, ?float $percentage = null): float;

    /**
     * @param  string $base
     * @param  string $target
     * @return void
     */
    public function updateExchangeRate(string $base, string $target): void;

    /**
     * @param  string     $baseCurrency
     * @param  float      $baseAmount
     * @param  string     $targetCurrency
     * @return float|null
     */
    public function getConversedAmount(string $baseCurrency, float $baseAmount, string $targetCurrency): ?float;

    /**
     * @param  array $excluded
     * @return array
     */
    public function getFilteredCurrencyCodes(array $excluded): array;

    /**
     * @param  string     $base
     * @param  string     $target
     * @return float|null
     */
    public function getInverseExchangeRate(string $base, string $target): ?float;

    /**
     * @param  string     $base
     * @param  string     $target
     * @return float|null
     */
    public function fetchExchangeRate(string $base, string $target): ?float;
}
