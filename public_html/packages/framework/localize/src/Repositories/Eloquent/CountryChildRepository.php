<?php

namespace MetaFox\Localize\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Localize\Models\Country;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Localize\Policies\CountryChildPolicy;
use MetaFox\Localize\Repositories\CountryChildRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class CountryChildRepository.
 *
 * @method CountryChild getModel()
 * @method CountryChild find($id, $columns = ['*'])
 */
class CountryChildRepository extends AbstractRepository implements CountryChildRepositoryInterface
{
    public function model(): string
    {
        return CountryChild::class;
    }

    public function createCountryChild(User $context, array $attributes): CountryChild
    {
        policy_authorize(CountryChildPolicy::class, 'create', $context);

        $state = new CountryChild($attributes);
        $state->save();
        $state->refresh();

        return $state;
    }

    public function updateCountryChild(User $context, int $id, array $attributes): CountryChild
    {
        policy_authorize(CountryChildPolicy::class, 'update', $context);
        $country = $this->find($id);
        $country->update($attributes);
        $country->refresh();

        return $country;
    }

    public function viewCountryChild(User $context, int $id): CountryChild
    {
        policy_authorize(CountryChildPolicy::class, 'view', $context);

        return $this->find($id);
    }

    public function viewCountryChildren(User $context, array $attributes, $usingId, $countryIdOrIso): Paginator
    {
        policy_authorize(CountryChildPolicy::class, 'viewAny', $context);
        $limit = $attributes['limit'] ?? 0;

        $countryIso = $usingId
            ? Country::query()->where('id', $countryIdOrIso)->value('country_iso')
            : $countryIdOrIso;

        return $this->getModel()->newQuery()
            ->where('country_iso', $countryIso)
            ->orderByDesc('ordering')
            ->orderBy('name')
            ->simplePaginate($limit);
    }

    public function deleteCountryChild(User $context, int $id): bool
    {
        policy_authorize(CountryChildPolicy::class, 'delete', $context);
        $country = $this->find($id);

        return (bool) $country->delete();
    }

    /**
     * @param  User                   $context
     * @param  array<string, mixed>   $orders
     * @return bool
     * @throws AuthorizationException
     */
    public function orderCountryChildren(User $context, array $orders): bool
    {
        policy_authorize(CountryChildPolicy::class, 'update', $context);

        foreach ($orders as $id => $order) {
            CountryChild::query()->where('id', $id)->update(['ordering' => $order]);
        }

        return true;
    }

    /**
     * @param  User                   $context
     * @param  array<string, mixed>   $attributes
     * @param  mixed                  $usingId
     * @param  mixed                  $countryIdOrIso
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteAllChildren(User $context, array $attributes, $usingId, $countryIdOrIso): bool
    {
        policy_authorize(CountryChildPolicy::class, 'delete', $context);

        $countryIso = $usingId
            ? Country::query()->where('id', $countryIdOrIso)->value('country_iso')
            : $countryIdOrIso;

        CountryChild::query()->where('country_iso', $countryIso)->delete();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCountryChildrenSuggestion(User $context, array $attribute): Collection
    {
        $countryIso = Arr::get($attribute, 'country');
        $search     = Arr::get($attribute, 'q');

        if (!$countryIso) {
            return collect();
        }

        $query = $this->getModel()->newModelQuery();
        if ($search) {
            $searchScope = new SearchScope();
            $searchScope->setFields(['name'])->setSearchText($search);
            $query = $query->addScope($searchScope);
        }

        return $query
            ->where('country_iso', $countryIso)
            ->orderByDesc('ordering')
            ->orderBy('name')
            ->get()
            ->collect();
    }
}
