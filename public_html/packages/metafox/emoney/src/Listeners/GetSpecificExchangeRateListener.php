<?php

namespace MetaFox\Emoney\Listeners;

use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;

class GetSpecificExchangeRateListener
{
    public function handle(string $base, string $target): ?float
    {
        return resolve(ConversionRateServiceInterface::class)->getExchangeRateWithInverse($base, $target);
    }
}
