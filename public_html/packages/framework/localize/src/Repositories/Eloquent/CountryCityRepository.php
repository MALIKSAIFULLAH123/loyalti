<?php

namespace MetaFox\Localize\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Localize\Models\CountryCity;
use MetaFox\Localize\Policies\CountryCityPolicy;
use MetaFox\Localize\Repositories\CountryCityRepositoryInterface;
use MetaFox\Localize\Support\CountryCity as SupportCountryCity;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Class CountryCityRepository.
 *
 * @method CountryCity getModel()
 * @method CountryCity find($id, $columns = ['*'])
 */
class CountryCityRepository extends AbstractRepository implements CountryCityRepositoryInterface
{
    public function model(): string
    {
        return CountryCity::class;
    }

    /**
     * @param  array<string, mixed> $attributes
     * @return Collection<Model>
     */
    public function viewCities(array $attributes): Collection
    {
        $search     = $attributes['q'] ?? '';
        $country    = $attributes['country'] ?? null;
        $state      = $attributes['state'] ?? null;
        $cityCode   = $attributes['city_code'] ?? null;
        $limit      = $attributes['limit'] ?? SupportCountryCity::CITY_SUGGESTION_LIMIT;

        $query = $this->getModel()->newQuery();

        if ($cityCode) {
            $query->where('core_country_cities.city_code', $cityCode);
        }

        if ($search) {
            $query->where('core_country_cities.name', $this->likeOperator(), $search . '%');
        }

        if ($country) {
            $query->whereHas('countryChild', function (Builder $q) use ($country) {
                $q->where('core_country_states.country_iso', '=', $country);
            });
        }

        if ($state) {
            $query->whereHas('countryChild', function (Builder $q) use ($state) {
                $q->where('core_country_states.state_iso', '=', $state);
            });
        }

        $activeStateCodes = Country::getAllActiveStates('state_code');

        $data = $query
            ->whereIn('core_country_cities.state_code', $activeStateCodes)
            ->orderByDesc('core_country_cities.ordering')
            ->orderBy('core_country_cities.name')
            ->limit($limit)
            ->get('core_country_cities.*');

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function createCity(User $context, array $attributes = []): CountryCity
    {
        policy_authorize(CountryCityPolicy::class, 'create', $context);
        $model = new CountryCity();
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function updateCity(User $context, int $id, array $attributes = []): CountryCity
    {
        policy_authorize(CountryCityPolicy::class, 'update', $context);
        $city = $this->find($id);

        $city->fill($attributes);
        $city->save();

        return $city;
    }

    /**
     * @inheritDoc
     */
    public function deleteCity(User $context, int $id): bool
    {
        $city = $this->find($id);

        return (bool) $city->delete();
    }
}
