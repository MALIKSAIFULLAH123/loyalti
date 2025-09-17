<?php

namespace MetaFox\EMoney\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\EMoney\Contracts\CurrencyConverterInterface;
use MetaFox\EMoney\Models\CurrencyConverter;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface CurrencyConverter.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface CurrencyConverterRepositoryInterface
{
    /**
     * @param  string $service
     * @return array
     */
    public function getConfig(string $service): array;

    /**
     * @param  string                          $service
     * @return CurrencyConverterInterface|null
     */
    public function getInstance(string $service): ?CurrencyConverterInterface;

    /**
     * @return Collection
     */
    public function viewConverters(): Collection;

    /**
     * @param  string                     $service
     * @return CurrencyConverterInterface
     * @throws ModelNotFoundException
     */
    public function getConverter(string $service): CurrencyConverter;

    /**
     * @return CurrencyConverterInterface
     */
    public function getDefaultProvider(): ?CurrencyConverterInterface;
}
