<?php

namespace MetaFox\Localize\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface CountryChildRepositoryInterface.
 *
 * @mixin BaseRepository
 * @method CountryChild getModel()
 * @method CountryChild find($id, $columns = ['*'])
 */
interface CountryChildRepositoryInterface
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return CountryChild
     * @throws AuthorizationException
     */
    public function viewCountryChild(User $context, int $id): CountryChild;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     * @param bool                 $usingId
     * @param mixed                $countryIdOrIso
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewCountryChildren(User $context, array $attributes, bool $usingId, mixed $countryIdOrIso): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return CountryChild
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createCountryChild(User $context, array $attributes): CountryChild;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return CountryChild
     * @throws AuthorizationException
     */
    public function updateCountryChild(User $context, int $id, array $attributes): CountryChild;

    /**
     * @param  User                   $context
     * @param  array                  $orders
     * @return bool
     * @throws AuthorizationException
     */
    public function orderCountryChildren(User $context, array $orders): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteCountryChild(User $context, int $id): bool;

    /**
     * @param User  $context
     * @param array $attributes
     * @param bool  $usingId
     * @param mixed $countryIdOrIso
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteAllChildren(User $context, array $attributes, bool $usingId, mixed $countryIdOrIso): bool;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attribute
     * @return Collection
     */
    public function getCountryChildrenSuggestion(User $context, array $attribute): Collection;
}
