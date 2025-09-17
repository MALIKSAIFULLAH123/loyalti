<?php

namespace MetaFox\Localize\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Models\CountryCity;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface CountryCityRepositoryInterface.
 *
 * @mixin BaseRepository
 * @method CountryCity getModel()
 * @method CountryCity find($id, $columns = ['*'])
 */
interface CountryCityRepositoryInterface
{
    /**
     * @param  array<string, mixed> $attributes
     * @return Collection<Model>
     */
    public function viewCities(array $attributes): Collection;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return CountryCity
     */
    public function createCity(User $context, array $attributes = []): CountryCity;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return CountryCity
     */
    public function updateCity(User $context, int $id, array $attributes = []): CountryCity;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteCity(User $context, int $id): bool;
}
