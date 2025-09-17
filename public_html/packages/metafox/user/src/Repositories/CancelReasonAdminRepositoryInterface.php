<?php

namespace MetaFox\User\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\CancelReason as Model;

/**
 * Interface CancelReason.
 *
 * @property Model $model
 * @method   Model getModel()
 * @method   Model find($id, $columns = ['*'])
 */
interface CancelReasonAdminRepositoryInterface
{
    public function getReasonsForForm(User $context): Collection;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewReasons(User $context, array $attributes = []): Paginator;

    /**
     * @param  User       $context
     * @param  array<int> $ids
     * @return bool
     */
    public function orderReasons(User $context, array $ids = []): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteReason(User $context, int $id): bool;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function createReason(User $context, array $attributes = []): Model;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function updateReason(User $context, int $id, array $attributes = []): Model;
}
