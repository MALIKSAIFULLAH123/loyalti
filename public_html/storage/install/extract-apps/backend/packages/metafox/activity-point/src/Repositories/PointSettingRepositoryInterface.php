<?php

namespace MetaFox\ActivityPoint\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\ActivityPoint\Models\PointSetting as Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Interface PointSettingRepositoryInterface.
 *
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 */
interface PointSettingRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Collection
     */
    public function viewSettings(User $context, array $attributes): Collection;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewSettingsAdmin(User $context, array $attributes): Paginator;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function updateSetting(User $context, int $id, array $attributes): Model;

    /**
     * @param  User  $context
     * @param  int   $id
     * @return Model
     */
    public function activateSetting(User $context, int $id): Model;

    /**
     * @param  User  $context
     * @param  int   $id
     * @return Model
     */
    public function deactivateSetting(User $context, int $id): Model;

    /**
     * @param  User       $user
     * @param  Entity     $resource
     * @param  string     $action
     * @return Model|null
     */
    public function getUserPointSetting(User $user, Entity $resource, string $action, int $type): ?Model;

    /**
     * @return array<int, mixed>
     */
    public function getModuleOptions(): array;

    /**
     * @params string $packageId
     * @return array<int, mixed>
     */
    public function getSettingActionsByPackageId(string $packageId): array;

    /**
     * @return Collection
     */
    public function getAllPointSetting(): Collection;

    /**
     * @return array
     */
    public function getAllPackageId(): array;

    /**
     * @param  int  $destRoleId
     * @param  int  $sourceRoleId
     * @return void
     */
    public function clonePointSettings(int $destRoleId, int $sourceRoleId): void;
}
