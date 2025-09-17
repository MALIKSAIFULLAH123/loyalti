<?php

namespace MetaFox\EMoney\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Contracts\CurrencyConverterInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\EMoney\Repositories\CurrencyConverterRepositoryInterface;
use MetaFox\EMoney\Models\CurrencyConverter;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class CurrencyConverterRepository.
 */
class CurrencyConverterRepository extends AbstractRepository implements CurrencyConverterRepositoryInterface
{
    public const CONVERTER_CACHE_ID = 'ewallet_converters';

    public function model()
    {
        return CurrencyConverter::class;
    }

    private function getItems(): array
    {
        return localCacheStore()->rememberForever(self::CONVERTER_CACHE_ID, function () {
            return $this->getModel()->newQuery()
                ->get()
                ->keyBy('service')
                ->toArray();
        });
    }

    private function getItem(string $service): ?array
    {
        $converters = $this->getItems();

        $target = Arr::get($converters, $service);

        if (!is_array($target)) {
            return null;
        }

        return $target;
    }

    public function getConfig(string $service): array
    {
        $service = $this->getItem($service);

        if (!is_array($service)) {
            return [];
        }

        $config = Arr::get($service, 'config');

        if (!is_array($config)) {
            return [];
        }

        return $config;
    }

    public function getInstance(string $service): ?CurrencyConverterInterface
    {
        $service = $this->getItem($service);

        if (!is_array($service)) {
            return null;
        }

        $class = Arr::get($service, 'service_class');

        if (!class_exists($class)) {
            return null;
        }

        $instance = resolve($class);

        if (!$instance instanceof CurrencyConverterInterface) {
            return null;
        }

        return $instance;
    }

    public function viewConverters(): Collection
    {
        return $this->getModel()->newQuery()
            ->orderByDesc('is_default')
            ->get();
    }

    public function getConverter(string $service): CurrencyConverter
    {
        return $this->getModel()->newQuery()
           ->where('service', $service)
           ->firstOrFail();
    }

    public function getDefaultProvider(): ?CurrencyConverterInterface
    {
        $defaultItem = $this->getDefaultItem();

        if (null === $defaultItem) {
            return null;
        }

        return $this->getInstance(Arr::get($defaultItem, 'service'));
    }

    protected function getDefaultItem(): ?array
    {
        $items = array_values($this->getItems());

        return Arr::first($items, function ($item) {
            return $item['is_default'];
        });
    }
}
