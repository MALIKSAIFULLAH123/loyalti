<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface IntegratedModule.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface IntegratedModuleRepositoryInterface
{
    /**
     * @param int $groupId
     * @return void
     */
    public function addModules(int $groupId): void;

    /**
     * @param int $groupId
     * @return Collection
     */
    public function getModules(int $groupId): Collection;

    /**
     * @param int $groupId
     * @return Collection
     */
    public function getModulesActive(int $groupId): Collection;

    /**
     * @param int   $groupId
     * @param array $params
     * @return bool
     */
    public function updateModule(User $context, int $groupId, array $params): bool;

    /**
     * @param User  $context
     * @param array $attributes
     * @return bool
     */
    public function orderModules(User $context, array $attributes): bool;

    /**
     * @param int $groupId
     * @return array
     */
    public function getProfileMenuSettings(int $groupId): array;
}
