<?php

namespace MetaFox\Localize\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Support\CacheManager;
use MetaFox\Localize\Contracts\CountryCitySupportContract;
use MetaFox\Localize\Models\CountryCity as Model;
use MetaFox\Localize\Repositories\CountryCityRepositoryInterface;
use MetaFox\Core\Support\Facades\Country;

class CountryCity implements CountryCitySupportContract
{
    public const CITY_SUGGESTION_LIMIT = 10;

    /**
     * @var array<string, Model>
     */
    private array $cities;

    private CountryCityRepositoryInterface $cityRepository;

    public function __construct(CountryCityRepositoryInterface $cityRepository)
    {
        $this->cityRepository = $cityRepository;
        $this->init();
    }

    public function getCacheName(): string
    {
        return CacheManager::CORE_COUNTRY_CITY_CACHE;
    }

    public function clearCache(): void
    {
        Cache::forget($this->getCacheName());
    }

    /**
     * @param  array<string, mixed>        $params
     * @return array<int,           mixed>
     */
    public function getCitySuggestions(array $params): array
    {
        return Cache::remember($this->getCacheName() . '_' . md5(serialize($params)), rand(3600, 43200), function () use ($params) {
            /** @var Collection $cities */
            return $this->cityRepository->viewCities($params)
                ->map(function (Model $city) {
                    return [
                        'label'         => $city->name,
                        'value'         => $city->city_code,
                        'id'            => $city->entityId(),
                        'name'          => $city->name,
                        'module_name'   => 'user',
                        'resource_name' => $city->entityType(),
                    ];
                })->toArray();
        });
    }

    protected function init(): void
    {
        $this->cities = Cache::remember(
            $this->getCacheName(),
            3000,
            function () {
                return Model::query()
                    ->orderBy('ordering')
                    ->orderBy('name')
                    ->get()
                    ->keyBy('city_code')
                    ->all();
            }
        );
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function getCity(string $cityCode): ?Model
    {
        return $this->cities[$cityCode] ?? null;
    }

    public function getDefaultCityCode(): string
    {
        return config('app.localize.city_iso');
    }

    /**
     * @inheritDoc
     */
    public function getAllActiveCities(mixed $pluck = null): array
    {
        $activeStateCodes = Country::getAllActiveStates('state_code');

        $data = collect($this->cities)->where(function ($citiData) use ($activeStateCodes) {
            $stateCode = $citiData->state_code ?? '';

            return in_array($stateCode, $activeStateCodes);
        });

        return match (gettype($pluck)) {
            'string' => $data->pluck($pluck)->toArray(),
            'array'  => !empty($pluck) ? $data->pluck($pluck[0], $pluck[1] ?? null)->toArray() : $data->toArray(),
            default  => $data->toArray()
        };
    }
}
