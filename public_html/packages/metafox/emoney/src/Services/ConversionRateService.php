<?php

namespace MetaFox\EMoney\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\EMoney\Contracts\CurrencyConverterInterface;
use MetaFox\EMoney\Models\ConversionRate;
use MetaFox\EMoney\Repositories\CurrencyConversionRateLogRepositoryInterface;
use MetaFox\EMoney\Repositories\CurrencyConverterRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Facades\Settings;

class ConversionRateService implements ConversionRateServiceInterface
{
    public const TARGET_CONVERSION_RATE_SETTING_CACHE_ID = 'ewallet_conversion_rate_%s_target_settings';

    public const BASE_CONVERSION_RATE_SETTING_CACHE_ID = 'ewallet_conversion_rate_%s_base_settings';

    public const CONVERSION_RATE_SETTING_CACHE_ID = 'ewallet_conversion_rate_settings';

    public function viewConversionRates(string $target = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): Collection
    {
        $baseCodes = $this->getBaseCurrencyCodesByTarget($target);

        if (!count($baseCodes)) {
            return collect([]);
        }

        $this->findMissing($target, $baseCodes);

        return ConversionRate::query()
            ->with(['log', 'log.converter'])
            ->where('target', $target)
            ->get();
    }

    public function findMissing(string $target, ?array $baseCodes = null): void
    {
        if (null === $baseCodes) {
            $baseCodes = $this->getBaseCurrencyCodesByTarget($target);
        }

        $missing = [];

        $existed = $this->getTargetSettings($target);

        foreach ($baseCodes as $baseCode) {
            $rate = Arr::get($existed, $baseCode);

            if (is_array($rate)) {
                continue;
            }

            $missing[] = $this->prepareData($baseCode, $target);
        }

        if (!is_array($missing)) {
            return;
        }

        ConversionRate::query()->upsert($missing, ['base', 'target'], ['type', 'exchange_rate', 'log_id']);
    }

    public function prepareData(string $base, string $target, string $type = Support::DEFAULT_TARGET_EXCHANGE_RATE_TYPE, ?float $exchangeRate = null, ?int $logId = null): array
    {
        return [
            'base'          => $base,
            'target'        => $target,
            'type'          => $type,
            'exchange_rate' => $exchangeRate,
            'log_id'        => $logId,
        ];
    }

    public function getExchangeRate(string $base, string $target): ?float
    {
        $rates = $this->getTargetSettings($target);

        $rate = Arr::get($rates, $base);

        if (null === $rate) {
            return null;
        }

        return Arr::get($rate, 'exchange_rate');
    }

    public function getExchangeRateWithInverse(string $baseCurrency, string $targetCurrency): ?float
    {
        $exchangeRate = $this->getExchangeRate($baseCurrency, $targetCurrency);

        if (null === $exchangeRate) {
            $exchangeRate = $this->getInverseExchangeRate($baseCurrency, $targetCurrency);
        }

        return $exchangeRate;
    }

    public function getBaseCurrencyCodesByTarget(string $target): array
    {
        return $this->getFilteredCurrencyCodes([$target]);
    }

    private function normalizeConversionRateForBrowse(string $base, string $target, array $setting): ?array
    {
        $type = __p('ewallet::admin.auto_synchronization');

        if (Arr::get($setting, 'type') == Support::TARGET_EXCHANGE_RATE_TYPE_MANUAL) {
            $type = __p('ewallet::admin.manual');
        }

        $updatedAt = Arr::get($setting, 'updated_at');

        if (null !== $updatedAt) {
            $updatedAt = Carbon::parse($updatedAt)->toISOString();
        }

        return [
            'base'                          => $base,
            'target'                        => $target,
            'type'                          => $type,
            'exchange_rate'                 => Arr::get($setting, 'exchange_rate'),
            'updated_at'                    => $updatedAt,
            'auto_synchronized_source'      => Arr::get($setting, 'auto_synchronized_source'),
            'auto_synchronized_source_link' => Arr::get($setting, 'auto_synchronized_source_link'),
        ];
    }

    public function getBaseSettings(string $base): array
    {
        return localCacheStore()->remember(sprintf(self::BASE_CONVERSION_RATE_SETTING_CACHE_ID, $base), 3600, function () use ($base) {
            return ConversionRate::query()
                ->where('base', $base)
                ->get()
                ->keyBy('target')
                ->toArray();
        });
    }

    public function getTargetSettings(string $target): array
    {
        return localCacheStore()->remember(sprintf(self::TARGET_CONVERSION_RATE_SETTING_CACHE_ID, $target), 3600, function () use ($target) {
            return ConversionRate::query()
                ->where('target', $target)
                ->get()
                ->keyBy('base')
                ->toArray();
        });
    }

    public function getSettings(): array
    {
        return localCacheStore()->remember(self::CONVERSION_RATE_SETTING_CACHE_ID, 3600, function () {
            $all = ConversionRate::query()
                ->get();

            $mapped = [];

            $all->each(function ($rate) use (&$mapped) {
                $mapped[$rate->target][$rate->base] = $rate->toArray();
            });

            return $mapped;
        });
    }

    public function updateSetting(int $id, array $attributes): ConversionRate
    {
        $rate = ConversionRate::query()->findOrFail($id);

        $rate->fill($attributes);

        $rate->save();

        if ($rate->type == Support::TARGET_EXCHANGE_RATE_TYPE_AUTO) {
            resolve(ConversionRateServiceInterface::class)->updateExchangeRate($rate->base, $rate->target);
        }

        $rate->refresh();

        return $rate;
    }

    public function updateTargetSettings(string $target, array $settings): void
    {
        ConversionRate::query()->upsert($settings, ['base', 'target'], ['type', 'exchange_rate', 'log_id', 'updated_at']);

        $this->clearTargetSettingCache($target);
    }

    public function updateBaseSettings(string $base, array $settings): void
    {
        ConversionRate::query()->upsert($settings, ['base', 'target'], ['type', 'exchange_rate', 'log_id', 'updated_at']);

        $this->clearBaseSettingCache($base);
    }

    public function getBalancePrice(float $price, string $base, string $target = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): ?array
    {
        $settings = $this->getTargetSettings($target);

        $setting = Arr::get($settings, $base);

        if (!is_array($setting)) {
            return null;
        }

        $exchangeRate = Arr::get($setting, 'exchange_rate');

        if (!is_numeric($exchangeRate)) {
            return null;
        }

        $exchangeRate = (float) $exchangeRate;

        return [
            'balance_price'        => $price * $exchangeRate,
            'balance_currency'     => $target,
            'exchange_rate'        => $exchangeRate,
            'exchange_rate_id'     => Arr::get($setting, 'id'),
            'exchange_rate_log_id' => Arr::get($setting, 'log_id'),
        ];
    }

    public function getCommissionFee(float $total, ?float $percentage = null): float
    {
        if (null === $percentage) {
            return 0;
        }

        if ($percentage <= 0) {
            return 0;
        }

        $fee = ($total * $percentage) / 100;

        return round($fee, 2);
    }

    private function clearTargetSettingCache(?string $target = null)
    {
        localCacheStore()->forget(self::CONVERSION_RATE_SETTING_CACHE_ID);

        if (is_string($target)) {
            localCacheStore()->forget(sprintf(self::TARGET_CONVERSION_RATE_SETTING_CACHE_ID, $target));
        }
    }

    private function clearBaseSettingCache(?string $base = null)
    {
        localCacheStore()->forget(self::CONVERSION_RATE_SETTING_CACHE_ID);

        if (is_string($base)) {
            localCacheStore()->forget(sprintf(self::BASE_CONVERSION_RATE_SETTING_CACHE_ID, $base));
        }
    }

    public function updateExchangeRate(string $base, string $target): void
    {
        $item = ConversionRate::query()
            ->where([
                'base'   => $base,
                'target' => $target,
            ])->firstOrFail();

        if (!$item->is_synchronized) {
            return;
        }

        $defaultProvider = resolve(CurrencyConverterRepositoryInterface::class)->getDefaultProvider();

        if (!$defaultProvider instanceof CurrencyConverterInterface) {
            return;
        }

        if (!$defaultProvider->isAvailable()) {
            return;
        }

        $rate = $defaultProvider->getExchangeRate($base, $target);

        if (null === $rate) {
            return;
        }

        $log = resolve(CurrencyConversionRateLogRepositoryInterface::class)->createLog($defaultProvider->getServiceName(), $base, $target, $rate, $defaultProvider->getPayload(), $defaultProvider->getResponse());

        $item->update([
            'exchange_rate' => $rate,
            'log_id'        => $log?->entityId(),
        ]);
    }

    public function getConversedAmount(string $baseCurrency, float $baseAmount, string $targetCurrency): ?float
    {
        if ($baseCurrency == $targetCurrency) {
            return $baseAmount;
        }

        try {
            $exchangeRate = $this->getExchangeRate($baseCurrency, $targetCurrency);

            if (null === $exchangeRate) {
                $exchangeRate = $this->fetchExchangeRate($baseCurrency, $targetCurrency);
            }

            if (null === $exchangeRate) {
                $exchangeRate = $this->getInverseExchangeRate($baseCurrency, $targetCurrency);
            }

            if (is_numeric($exchangeRate)) {
                return round($baseAmount * $exchangeRate, 2);
            }
        } catch (\Throwable $exception) {}

        return null;
    }

    public function getInverseExchangeRate(string $base, string $target): ?float
    {
        $rate = $this->getExchangeRate($target, $base);

        if (null === $rate) {
            return null;
        }

        if ($rate == 0) {
            return null;
        }

        return round(1 / $rate, Support::MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER);
    }

    public function getFilteredCurrencyCodes(array $excluded): array
    {
        $currencies = app('currency')->getCurrencies();

        return array_filter(array_keys($currencies), function ($code) use ($excluded) {
            return !in_array($code, $excluded);
        });
    }

    public function fetchExchangeRate(string $base, string $target): ?float
    {
        /**
         * @var ConversionRate $rate
         */
        $rate = ConversionRate::query()
            ->firstOrCreate([
                'base'   => $base,
                'target' => $target,
            ], [
                'type' => Support::DEFAULT_TARGET_EXCHANGE_RATE_TYPE,
            ]);

        if (!$rate->is_synchronized) {
            return null;
        }

        try {
            $defaultProvider = resolve(CurrencyConverterRepositoryInterface::class)->getDefaultProvider();

            if (!$defaultProvider instanceof CurrencyConverterInterface) {
                return null;
            }

            if (!$defaultProvider->isAvailable()) {
                return null;
            }

            $exchangeRate = $defaultProvider->getExchangeRate($base, $target);

            if (null === $exchangeRate) {
                return null;
            }

            $log = resolve(CurrencyConversionRateLogRepositoryInterface::class)->createLog($defaultProvider->getServiceName(), $base, $target, $exchangeRate, $defaultProvider->getPayload(), $defaultProvider->getResponse());

            $rate->update([
                'exchange_rate' => $exchangeRate,
                'log_id'        => $log->entityId(),
            ]);

            return $exchangeRate;
        } catch (\Throwable $exception) {
        }

        return null;
    }
}
