<?php

namespace MetaFox\EMoney\Repositories;

use MetaFox\EMoney\Models\CurrencyConversionRateLog;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface CurrencyConversionRateLog.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface CurrencyConversionRateLogRepositoryInterface
{
    /**
     * @param  string                    $service
     * @param  string                    $src
     * @param  string                    $dest
     * @param  float                     $exchangeRate
     * @param  array|null                $payload
     * @param  array|null                $response
     * @return CurrencyConversionRateLog
     */
    public function createLog(string $service, string $src, string $dest, float $exchangeRate, ?array $payload, ?array $response): CurrencyConversionRateLog;
}
