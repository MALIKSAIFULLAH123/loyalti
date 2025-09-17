<?php

namespace MetaFox\EMoney\Listeners;

use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;

class GetExchangeRateListener
{
    public function handle(string $base): ?float
    {
        $target = app('currency')->getDefaultCurrencyId();

        if ($target == $base) {
            return 0;
        }

        return resolve(ConversionRateServiceInterface::class)->getExchangeRate($base, $target);
    }
}
