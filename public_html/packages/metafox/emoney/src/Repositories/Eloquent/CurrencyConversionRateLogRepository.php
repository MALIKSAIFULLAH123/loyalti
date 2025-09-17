<?php

namespace MetaFox\EMoney\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\EMoney\Repositories\CurrencyConversionRateLogRepositoryInterface;
use MetaFox\EMoney\Models\CurrencyConversionRateLog;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class CurrencyConversionRateLogRepository.
 */
class CurrencyConversionRateLogRepository extends AbstractRepository implements CurrencyConversionRateLogRepositoryInterface
{
    public function model()
    {
        return CurrencyConversionRateLog::class;
    }

    public function createLog(string $service, string $src, string $dest, float $exchangeRate, ?array $payload, ?array $response): CurrencyConversionRateLog
    {
        if (is_array($response)) {
            $response = json_encode($response);
        }

        if (is_array($payload)) {
            $payload = json_encode($payload);
        }

        $attributes = [
            'service'       => $service,
            'from'          => $src,
            'to'            => $dest,
            'exchange_rate' => $exchangeRate,
            'payload'       => $payload,
            'response'      => $response,
        ];

        $log = $this->getModel()->newInstance($attributes);

        $log->save();

        return $log->refresh();
    }
}
