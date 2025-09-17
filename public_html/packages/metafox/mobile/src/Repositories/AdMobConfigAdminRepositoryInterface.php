<?php

namespace MetaFox\Mobile\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;
use MetaFox\Mobile\Models\AdMobConfig as Model;

/**
 * Interface AdMobConfig.
 *
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 */
interface AdMobConfigAdminRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function createConfig(User $context, array $attributes = []): Model;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function updateConfig(User $context, int $id, array $attributes = []): Model;

    /**
     * @param  User $context,
     * @param  int  $id
     * @return bool
     */
    public function deleteConfig(User $context, int $id): bool;
}
