<?php

namespace MetaFox\EMoney\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Contracts\CurrencyConverterInterface;
use MetaFox\EMoney\Models\ConversionRate;
use MetaFox\EMoney\Repositories\CurrencyConversionRateLogRepositoryInterface;
use MetaFox\EMoney\Support\Support;

class GetExchangeRateForBaseJob extends BaseExchangeRate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private string $base = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE)
    {
        parent::__construct();
    }

    public function handle()
    {
        $codes = $this->getCurrencyCodes();

        if (!count($codes)) {
            return;
        }

        if (null === ($defaultProvider = $this->getProvider())) {
            return;
        }

        $settings = $this->getSettings();

        $autoSyncCodes = $this->filterSynchronizedCurrencyCodes($codes, $settings);

        $settings = $this->fetchExchangeRates($defaultProvider, $settings, $codes, $autoSyncCodes);

        $this->updateSettings($settings);
    }

    protected function fetchExchangeRates(CurrencyConverterInterface $defaultProvider, array $settings, array $codes, array $autoSyncCodes): array
    {
        /**
         * @var CurrencyConversionRateLogRepositoryInterface $logRepository
         */
        $logRepository = resolve(CurrencyConversionRateLogRepositoryInterface::class);

        $updatedAt = (new ConversionRate())->freshTimestamp();

        foreach ($codes as $code) {
            $rate = $defaultProvider->getExchangeRate($this->base, $code);

            if (null === $rate) {
                continue;
            }

            $log = $logRepository->createLog($defaultProvider->getServiceName(), $this->base, $code, $rate, $defaultProvider->getPayload(), $defaultProvider->getResponse());

            if (!in_array($code, $autoSyncCodes)) {
                continue;
            }

            $data = array_merge(
                $this->resolveService()->prepareData(
                    $this->base,
                    $code,
                    Support::TARGET_EXCHANGE_RATE_TYPE_AUTO,
                    $rate,
                    $log->entityId()
                ),
                ['updated_at' => $updatedAt]
            );

            $settings[$code] = array_merge(Arr::get($settings, $code, []), $data);
        }

        return $settings;
    }

    protected function updateSettings(array $settings): void
    {
        /**
         * Ensure we use the same data for upsert
         */
        $settings = array_map(function ($setting) {
            if (!Arr::has($setting, 'id')) {
                return $setting;
            }

            Arr::forget($setting, ['id']);

            return $setting;
        }, $settings);

        $this->resolveService()->updateBaseSettings($this->base, $settings);
    }

    protected function getSettings(): array
    {
        return $this->resolveService()->getBaseSettings($this->base);
    }

    protected function getCurrencyCodes(): array
    {
        return $this->resolveService()->getFilteredCurrencyCodes([$this->base]);
    }
}
