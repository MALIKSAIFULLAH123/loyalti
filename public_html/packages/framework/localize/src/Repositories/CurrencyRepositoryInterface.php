<?php

namespace MetaFox\Localize\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use MetaFox\Localize\Models\Currency;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface CurrencyRepositoryInterface.
 * @mixin BaseRepository
 * @method Currency getModel()
 * @method Currency find($id, $columns = ['*'])
 */
interface CurrencyRepositoryInterface
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return Currency
     * @throws AuthorizationException
     */
    public function viewCurrency(User $context, int $id): Currency;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewCurrencies(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Currency
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createCurrency(User $context, array $attributes): Currency;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Currency
     * @throws AuthorizationException
     */
    public function updateCurrency(User $context, int $id, array $attributes): Currency;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteCurrency(User $context, int $id): bool;

    /**
     * @param User            $context
     * @param array<int, int> $orders
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function orderCurrencies(User $context, array $orders): bool;

    /**
     * @param User $context
     * @param int  $id
     * @param int  $isActive
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function updateActive(User $context, int $id, int $isActive): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function markAsDefault(User $context, int $id): bool;

    /**
     * @param  User       $context
     * @return Collection
     */
    public function getAllActiveCurrencies(User $context): Collection;

    /**
     * @return Currency|null
     */
    public function getDefaultCurrency(): ?Currency;
}
