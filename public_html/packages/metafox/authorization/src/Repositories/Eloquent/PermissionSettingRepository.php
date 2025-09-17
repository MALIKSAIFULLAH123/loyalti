<?php

namespace MetaFox\Authorization\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Authorization\Repositories\PermissionSettingRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\UserRole;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class PermissionSettingRepository extends AbstractRepository implements PermissionSettingRepositoryInterface
{
    public function model()
    {
        return Permission::class;
    }

    public function installSettingsFromApps(): bool
    {
        $response = PackageManager::discoverSettings('getUserPermissions');

        if (!is_array($response) || empty($response)) {
            return false;
        }

        foreach ($response as $moduleId => $settings) {
            if (empty($settings) || !is_string($moduleId)) {
                continue;
            }

            $this->installSettings($moduleId, $settings);
        }

        return true;
    }

    public function installSettings(string $moduleId, array $resourceSettings): bool
    {
        foreach ($resourceSettings as $entityType => $settings) {
            if (empty($settings) || !is_string($entityType)) {
                continue;
            }

            foreach ($settings as $permissionName => $roles) {
                $attributes = [];

                if (Arr::has($roles, 'roles')) {
                    $attributes = $roles;

                    $roles = Arr::get($roles, 'roles');

                    Arr::forget($attributes, 'roles');
                }

                $params = array_merge([
                    'name'          => sprintf('%s.%s', $entityType, $permissionName),
                    'guard_name'    => Role::DEFAULT_GUARD,
                    'module_id'     => $moduleId,
                    'entity_type'   => $entityType,
                    'action'        => $permissionName,
                    'default_value' => $settings['default'] ?? null,
                    'data_type'     => $settings['type'] ?? MetaFoxDataType::BOOLEAN,
                    'is_public'     => 1,
                    'is_editable'   => 1,
                ], $attributes);

                $permission = $this->findByModuleAndName($moduleId, sprintf('%s.%s', $entityType, $permissionName));

                $isNewPermission = false;

                if (!$permission) {
                    $permission      = new Permission();
                    $isNewPermission = true;
                }

                $permission->fill($params);
                $permission->save();

                // If the permission has already been installed, its roles should not be overridden
                if ($isNewPermission && is_array($roles) && count($roles)) {
                    $permission->assignRole($roles);

                    resolve(PermissionRepositoryInterface::class)->initializeDefaultPermissionForCustomRoles($permission, $roles);
                }
            }
        }

        return true;
    }

    public function installValueSettingsFromApps(): bool
    {
        $response = PackageManager::discoverSettings('getUserValuePermissions');

        if (!is_array($response) || empty($response)) {
            return false;
        }

        foreach ($response as $moduleId => $settings) {
            if (empty($settings) || !is_string($moduleId)) {
                continue;
            }

            $this->installValueSettings($moduleId, $settings);
        }

        return true;
    }

    public function installValueSettings(string $moduleId, array $resourceSettings): bool
    {
        foreach ($resourceSettings as $entityType => $settings) {
            if (empty($settings) || !is_string($entityType)) {
                continue;
            }

            foreach ($settings as $permissionName => $setting) {
                $values = [
                    'name'          => sprintf('%s.%s', $entityType, $permissionName),
                    'guard_name'    => Role::DEFAULT_GUARD,
                    'module_id'     => $moduleId,
                    'entity_type'   => $entityType,
                    'action'        => Arr::get($setting, 'action', $permissionName),
                    'data_type'     => $setting['type'] ?? MetaFoxDataType::INTEGER,
                    'default_value' => $setting['default'] ?? null,
                    'is_public'     => Arr::get($setting, 'is_public', 1),
                    'is_editable'   => Arr::get($setting, 'is_editable', 1),
                    'extra'         => $setting['extra'] ?? null,
                ];

                $permission      = $this->findByModuleAndName($moduleId, sprintf('%s.%s', $entityType, $permissionName));
                $isNewPermission = false;

                if (!$permission) {
                    $permission      = new Permission();
                    $isNewPermission = true;
                }

                $permission->fill($values);
                $permission->save();

                if ($isNewPermission && !empty($setting['roles'])) {
                    $permission->assignRoleWithPivot($setting['roles']);

                    resolve(PermissionRepositoryInterface::class)->initializeDefaultPermissionForCustomRoles($permission, $setting['roles']);
                }
            }
        }

        return true;
    }

    public function getPermissions(Role $role): array
    {
        $collection = $this->getModel()->newInstance()
            ->newQuery()
            ->whereIn('module_id', resolve('core.packages')->getActivePackageAliases())
            ->where('is_public', MetaFoxConstant::IS_PUBLIC)
            ->get()
            ->groupBy('entity_type');

        $data = [];

        foreach ($collection as $entityType => $permissions) {
            /** @var Permission[] $permissions */
            foreach ($permissions as $permission) {
                $data[$permission->module_id][$entityType][$permission->action] = match ($permission->data_type) {
                    MetaFoxDataType::BOOLEAN => $role->hasPermissionTo($permission),
                    MetaFoxDataType::INTEGER => (int) $role->getPermissionValue($permission),
                    default                  => $role->getPermissionValue($permission)
                };
            }
        }

        Arr::set($data, 'user.user.isLoggedIn', $role->entityId() !== UserRole::GUEST_USER);
        Arr::set($data, 'user.user.isGuest', $role->entityId() === UserRole::GUEST_USER);

        return $data;
    }

    public function getExcludedActions(): array
    {
        return ['flood_control', 'quota_control', 'attachment_type_allow'];
    }

    public function rollDownPermissions(string $moduleId, array $notIn): void
    {
        Permission::query()
            ->where('module_id', $moduleId)
            ->whereNotIn('name', $notIn)
            ->update([
                'is_public'   => 0,
                'is_editable' => 0,
            ]);
    }

    public function deletePermission(
        string $moduleId,
        string $name,
        string $entityType,
        string $guard = Role::DEFAULT_GUARD
    ): bool {
        $permission = $this->getModel()->newQuery()
            ->where([
                'module_id'   => $moduleId,
                'action'      => $name,
                'entity_type' => $entityType,
                'guard_name'  => $guard,
            ])
            ->first();

        if (!$permission instanceof Permission) {
            return false;
        }

        $permission->delete();

        $this->deleteRelatedData($permission);

        return true;
    }

    protected function deleteRelatedData(Permission $permission): void
    {
        switch ($permission->data_type) {
            case MetaFoxDataType::BOOLEAN:
                $permission->rolesHasPermissions()->sync([]);
                break;
            case MetaFoxDataType::INTEGER:
                $permission->rolesHasValuePermissions()->sync([]);
                break;
        }
    }

    /**
     * Get the permission by moduleId and name.
     *
     * @param string $moduleId
     * @param string $name
     *
     * @return Permission|null
     */
    private function findByModuleAndName(string $moduleId, string $name): ?Permission
    {
        return Permission::query()->where([
            'name'       => $name,
            'guard_name' => Role::DEFAULT_GUARD,
            'module_id'  => $moduleId,
        ])->first();
    }
}
