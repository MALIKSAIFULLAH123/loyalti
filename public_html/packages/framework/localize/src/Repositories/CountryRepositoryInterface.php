<?php

namespace MetaFox\Localize\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Localize\Models\Country;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface CountryRepositoryInterface.
 * @mixin BaseRepository
 * @method Country getModel()
 * @method Country find($id, $columns = ['*'])
 */
interface CountryRepositoryInterface
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return Country
     * @throws AuthorizationException
     */
    public function viewCountry(User $context, int $id): Country;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewCountries(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Country
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createCountry(User $context, array $attributes): Country;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Country
     * @throws AuthorizationException
     */
    public function updateCountry(User $context, int $id, array $attributes): Country;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteCountry(User $context, int $id): bool;

    /**
     * @param User  $context
     * @param int[] $orders
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function orderCountries(User $context, array $orders): bool;

    /**
     * @param User  $context
     * @param int[] $ids
     * @param int   $active
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function batchActiveCountries(User $context, array $ids, int $active): bool;
}
