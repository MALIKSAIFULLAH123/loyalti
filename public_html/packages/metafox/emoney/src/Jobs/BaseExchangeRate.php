<?php

namespace MetaFox\EMoney\Jobs;

use Illuminate\Support\Arr;
use MetaFox\EMoney\Contracts\CurrencyConverterInterface;
use MetaFox\EMoney\Repositories\CurrencyConverterRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Jobs\AbstractJob;

class BaseExchangeRate extends AbstractJob
{
    protected function getProvider(): ?CurrencyConverterInterface
    {
        $defaultProvider = resolve(CurrencyConverterRepositoryInterface::class)->getDefaultProvider();

        if (!$defaultProvider instanceof CurrencyConverterInterface) {
            return null;
        }

        if (!$defaultProvider->isAvailable()) {
            return null;
        }

        return $defaultProvider;
    }

    protected function filterSynchronizedCurrencyCodes(array $codes, array $settings): array
    {
        return array_filter($codes, function ($code) use ($settings) {
            return Arr::get($settings, sprintf('%s.type', $code), Support::DEFAULT_TARGET_EXCHANGE_RATE_TYPE) == Support::TARGET_EXCHANGE_RATE_TYPE_AUTO;
        });
    }

    protected function resolveService(): ConversionRateServiceInterface
    {
        return resolve(ConversionRateServiceInterface::class);
    }
}
