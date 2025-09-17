<?php
namespace MetaFox\Featured\Services;

use Illuminate\Support\Arr;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Featured\Services\Contracts\SettingServiceInterface;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;

class SettingService implements SettingServiceInterface
{
    public const CACHE_ID = 'featured_settings_role_%s';

    public function __construct(protected PermissionRepositoryInterface $permissionRepository, protected RoleRepositoryInterface $roleRepository)
    {
    }

    public function getSettings(int $roleId): array
    {
        return localCacheStore()->rememberForever(sprintf(self::CACHE_ID, $roleId), function () use ($roleId) {
            $builder = $this->permissionRepository->getPermissionBuilder([
                'is_public' => 0,
                'is_editable' => 1,
                'actions' => ['feature', 'purchase_feature'],
            ]);

            $permissions = $builder->orderBy('entity_type')
                ->orderBy('id')
                ->get();

            if (!$permissions->count()) {
                return [];
            }

            $role = $this->roleRepository->find($roleId);

            return $permissions->groupBy('entity_type')
                ->map(function (\Illuminate\Database\Eloquent\Collection $collection) use ($role) {
                    return $collection->keyBy('action')
                        ->map(function (Permission $permission) use ($role) {
                            return $role->hasPermissionTo($permission->name);
                        });
                })
                ->toArray();
        });
    }

    public function updateSettings(User $context, Role $role, array $settings): bool
    {
        if (!count($settings)) {
            return false;
        }

        $settings = Arr::dot($settings);

        $this->permissionRepository->updatePermissionValue($context, $role, $settings);

        return true;
    }

    public function getPermissionsByName(array $settings): Collection
    {
        $settings = Arr::dot($settings);

        $names    = array_keys($settings);

        if (!count($names)) {
            return collect();
        }

        return $this->permissionRepository->getPermissionBuilder([
                'is_public' => 0,
                'is_editable' => 1,
                'actions' => ['feature', 'purchase_feature'],
            ])
            ->get()
            ->keyBy('name');
    }

    protected function setPermissionRepository(PermissionRepositoryInterface $permissionRepository): void
    {
        $this->permissionRepository = $permissionRepository;
    }

    protected function setRoleRepository(RoleRepositoryInterface $roleRepository): void
    {
        $this->roleRepository = $roleRepository;
    }
}
