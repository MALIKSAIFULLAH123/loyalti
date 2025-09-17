<?php

namespace MetaFox\Localize\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use MetaFox\Localize\Models\Currency;
use MetaFox\Localize\Policies\CurrencyPolicy;
use MetaFox\Localize\Repositories\CurrencyRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class CurrencyRepository.
 * @method Currency getModel()
 * @method Currency find($id, $columns = ['*'])
 */
class CurrencyRepository extends AbstractRepository implements CurrencyRepositoryInterface
{
    public function model(): string
    {
        return Currency::class;
    }

    public function createCurrency(User $context, array $attributes): Currency
    {
        policy_authorize(CurrencyPolicy::class, 'create', $context);

        /** @var Currency $currency */
        $currency = parent::create($attributes);

        $currency->refresh();

        if (Arr::get($attributes, 'is_default', false)) {
            $this->getModel()->newQuery()
                ->where('id', '<>', $currency->entityId())
                ->where('is_default', '=', true)
                ->update(['is_default' => false]);
        }

        return $currency;
    }

    public function updateCurrency(User $context, int $id, array $attributes): Currency
    {
        policy_authorize(CurrencyPolicy::class, 'update', $context);

        $currency = $this->find($id);

        $isDefault = Arr::get($attributes, 'is_default', false);

        if ($isDefault) {
            Arr::set($attributes, 'is_active', true);
        }

        $currency->update($attributes);

        $currency->refresh();

        if ($isDefault) {
            $this->getModel()->newQuery()
                ->where(['is_default' => true])
                ->where('id', '<>', $id)
                ->update(['is_default' => false]);
        }

        return $currency;
    }

    public function viewCurrency(User $context, int $id): Currency
    {
        policy_authorize(CurrencyPolicy::class, 'view', $context);

        return $this->find($id);
    }

    public function viewCurrencies(User $context, array $attributes): Paginator
    {
        policy_authorize(CurrencyPolicy::class, 'viewAny', $context);

        $query = $this->getModel()->newQuery();

        if ($q = $attributes['q'] ?? null) {
            $query = $query->addScope(new SearchScope($q, ['name', 'symbol']));
        }

        return $query
            ->orderBy('ordering')
            ->orderBy('name')
            ->paginate($attributes['limit'] ?? 0);
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function deleteCurrency(User $context, int $id): bool
    {
        policy_authorize(CurrencyPolicy::class, 'delete', $context);
        $currency = $this->find($id);
        if ($currency->is_default) {
            throw ValidationException::withMessages([
                __p('core::validation.cant_delete_default_currency'),
            ]);
        }

        $currency->delete();

        return true;
    }

    public function orderCurrencies(User $context, array $orders): bool
    {
        policy_authorize(CurrencyPolicy::class, 'update', $context);

        foreach ($orders as $id => $order) {
            Currency::query()->where('id', $id)->update(['ordering' => $order]);
        }

        return true;
    }

    /**
     * @param User $context
     * @param int  $id
     * @param int  $isActive
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function updateActive(User $context, int $id, int $isActive): bool
    {
        policy_authorize(CurrencyPolicy::class, 'update', $context);

        $currency = $this->find($id);

        if ($currency->is_default) {
            return false;
        }

        $currency->update(['is_active' => $isActive]);

        return true;
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function markAsDefault(User $context, int $id): bool
    {
        policy_authorize(CurrencyPolicy::class, 'update', $context);

        $currency = $this->find($id);

        if ($currency->is_default) {
            return true;
        }

        $updateData = [];
        $cursor     = $this->getModel()->newQuery()->orWhereNotIn('id', [$id])->cursor();

        foreach ($cursor as $item) {
            if (!$item instanceof Currency) {
                continue;
            }

            $updateData[] = [
                'code'       => $item->code,
                'is_default' => false,
                'ordering'   => $item->ordering + 1,
                'symbol'     => $item->symbol,
                'format'     => $item->format,
                'name'       => $item->name,
            ];
        }

        $this->getModel()->newQuery()->upsert($updateData, ['code'], ['is_default', 'ordering']);

        return $currency->update(['is_active' => true, 'is_default' => true, 'ordering' => 1]);
    }

    public function getAllActiveCurrencies(User $context): Collection
    {
        policy_authorize(CurrencyPolicy::class, 'viewAny', $context);

        return $this->getModel()->newQuery()
            ->where('is_active', '=', Currency::IS_ACTIVE)
            ->orderBy('ordering')
            ->orderBy('name')
            ->get();
    }

    public function getDefaultCurrency(): ?Currency
    {
        /** @var Currency $currency */
        $currency = $this->getModel()->newQuery()
            ->where('is_active', '=', Currency::IS_ACTIVE)
            ->where('is_default', '=', Currency::IS_DEFAULT)
            ->first();

        return $currency;
    }
}
