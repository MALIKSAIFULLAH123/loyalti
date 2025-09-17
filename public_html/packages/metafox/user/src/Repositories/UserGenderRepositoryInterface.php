<?php

namespace MetaFox\User\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserGender as Model;

/**
 * Interface UserGenderRepositoryInterface.
 *
 * stub: /packages/repositories/interface.stub
 *
 * @method Model getModel();
 */
interface UserGenderRepositoryInterface
{
    /**
     * @param  string $phrase
     * @return Model
     */
    public function findGenderByPhrase(string $phrase): ?Model;
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewGenders(User $context, array $attributes): Paginator;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function createGender(User $context, array $attributes): Model;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function updateGender(User $context, int $id, array $attributes): Model;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteGender(User $context, int $id): bool;

    /**
     * @param  User                             $context
     * @param  array<string, mixed>|null        $where
     * @return array<int, array<string, mixed>>
     */
    public function getForForms(User $context, ?array $where = null): array;

    /**
     * @param  array<string, mixed>             $params
     * @return array<int, array<string, mixed>>
     */
    public function getSuggestion(array $params): array;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return LengthAwarePaginator
     */
    public function viewGendersForAdmin(User $context, array $attributes): LengthAwarePaginator;

    /**
     * @return array'
     */
    public function getGenderOptions(): array;

    /**
     * @param  array      $ids
     * @return Collection
     */
    public function viewAllGenders(array $ids = []): Collection;
}
