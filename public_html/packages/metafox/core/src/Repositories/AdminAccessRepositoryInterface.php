<?php

namespace MetaFox\Core\Repositories;

use MetaFox\Core\Models\AdminAccess as Model;
use MetaFox\Platform\Contracts\User;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * Interface AdminAccess.
 *
 * @method Model getModel();
 * @method Model find($id, $columns = ['*'])
 */
interface AdminAccessRepositoryInterface
{
    /**
     * @param  User                 $user
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function logAccess(User $user, array $attributes = []): Model;

    /**
     * @param  int       $limit
     * @return Paginator
     */
    public function getLatestAccesses(int $limit): Paginator;

    /**
     * @param  User      $context
     * @param  int       $limit
     * @return Paginator
     */
    public function getActiveUsers(User $context, int $limit): Paginator;
}
