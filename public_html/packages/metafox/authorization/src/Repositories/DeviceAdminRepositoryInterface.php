<?php

namespace MetaFox\Authorization\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Authorization\Models\UserDevice as Model;

/**
 * Interface DeviceRepositoryInterface.
 *
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 */
interface DeviceAdminRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function updateDevice(User $context, int $id, array $attributes = []): Model;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewDevices(User $context, array $attributes = []): Paginator;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteDevice(User $context, int $id): bool;
}
