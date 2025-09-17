<?php

namespace MetaFox\Localize\Contracts;

use MetaFox\Localize\Models\CountryCity as Model;

/**
 * Interface CountryCitySupportContract.
 */
interface CountryCitySupportContract
{
    /**
     * @param  array<string, mixed>        $params
     * @return array<int,           mixed>
     */
    public function getCitySuggestions(array $params): array;

    /**
     * @return array<Model>
     */
    public function getCities(): array;

    /**
     * @param string $cityCode
     *
     * @return Model|null
     */
    public function getCity(string $cityCode): ?Model;

    /**
     * @return string
     */
    public function getDefaultCityCode(): string;

    /**
     * @param  string|array<string>|null $pluck
     * @return array<Model>
     */
    public function getAllActiveCities(mixed $pluck = null): array;
}
