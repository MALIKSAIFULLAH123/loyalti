<?php

namespace MetaFox\EMoney\Listeners;

use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;

class GetConversedAmountListener
{
    public function handle(string $baseCurrency, float $baseAmount, string $targetCurrency): ?float
    {
        return resolve(ConversionRateServiceInterface::class)->getConversedAmount($baseCurrency, $baseAmount, $targetCurrency);
    }
}
