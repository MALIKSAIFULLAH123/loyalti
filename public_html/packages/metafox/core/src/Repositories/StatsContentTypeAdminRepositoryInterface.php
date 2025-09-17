<?php

namespace MetaFox\Core\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Core\Models\StatsContentType as Model;

/**
 * Interface StatsContentType.
 *
 * @mixin BaseRepository
 * @method Model find($id, $columns = ['*'])
 */
interface StatsContentTypeAdminRepositoryInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAllKeyByName(): array;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewTypes(User $context, array $attributes = []): Collection;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function updateType(User $context, int $id, array $attributes = []): Model;

    /**
     * Used for ordering types.
     *
     * @param  array<int> $ids
     * @return bool
     */
    public function orderTypes(array $ids): bool;
}
