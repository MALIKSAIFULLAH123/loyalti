<?php

namespace MetaFox\Localize\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Localize\Models\Country;
use MetaFox\Localize\Policies\CountryPolicy;
use MetaFox\Localize\Repositories\CountryRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class CountryRepository.
 * @method Country getModel()
 * @method Country find($id, $columns = ['*'])
 */
class CountryRepository extends AbstractRepository implements CountryRepositoryInterface
{
    public function model(): string
    {
        return Country::class;
    }

    public function createCountry(User $context, array $attributes): Country
    {
        policy_authorize(CountryPolicy::class, 'create', $context);

        /** @var Country $country */
        $country = parent::create($attributes);
        $country->refresh();

        return $country;
    }

    public function updateCountry(User $context, int $id, array $attributes): Country
    {
        policy_authorize(CountryPolicy::class, 'update', $context);
        $country = $this->find($id);
        $country->update($attributes);
        $country->refresh();

        return $country;
    }

    public function viewCountry(User $context, int $id): Country
    {
        policy_authorize(CountryPolicy::class, 'view', $context);

        return $this->find($id);
    }

    public function viewCountries(User $context, array $attributes): Paginator
    {
        policy_authorize(CountryPolicy::class, 'viewAny', $context);

        $limit  = Arr::get($attributes, 'limit', 10);
        $search = Arr::get($attributes, 'q');

        $query = $this->getModel()->newQuery();

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['name', 'country_iso']));
        }

        return $query->orderByDesc('ordering')
            ->orderBy('name')
            ->paginate($limit);
    }

    public function deleteCountry(User $context, int $id): bool
    {
        policy_authorize(CountryPolicy::class, 'delete', $context);
        $country = $this->find($id);

        return (bool) $country->delete();
    }

    public function orderCountries(User $context, array $orders): bool
    {
        policy_authorize(CountryPolicy::class, 'update', $context);

        foreach ($orders as $id => $order) {
            Country::query()->where('id', $id)->update(['ordering' => $order]);
        }

        return true;
    }

    public function batchActiveCountries(User $context, array $ids, int $active): bool
    {
        policy_authorize(CountryPolicy::class, 'update', $context);

        foreach ($ids as $id) {
            Country::query()->where('id', $id)->update(['is_active' => $active]);
        }

        return true;
    }
}
