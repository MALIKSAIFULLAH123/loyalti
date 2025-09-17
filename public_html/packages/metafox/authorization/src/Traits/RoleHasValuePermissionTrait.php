<?php

namespace MetaFox\Authorization\Traits;

use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\MetaFoxDataType;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin HasRoles
 */
trait RoleHasValuePermissionTrait
{
    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return Permission
     *
     * @throws GuardDoesNotMatch
     */
    private function findPermissionValue($permission): Permission
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, 'api');
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, 'api');
        }
        /**
         * @var Permission $permission
         */
        if (MetaFoxDataType::BOOLEAN == $permission->data_type) {
            throw PermissionDoesNotExist::create($permission->name, $this->getGuardNames());
        }

        if (!$this->getGuardNames()->contains($permission->guard_name)) {
            throw GuardDoesNotMatch::create($permission->guard_name, $this->getGuardNames());
        }

        return $permission;
    }

    public function hasPermissionValue($permission): bool
    {
        $permission = $this->findPermissionValue($permission);

        return $this->valuePermissions->contains('id', $permission->entityId());
    }

    public function getRole(): ?Role
    {
        return $this;
    }

    /**
     * @param Permission $permission
     *
     * @return mixed|null
     */
    private function getDefaultValue(Permission $permission)
    {
        return $permission->default_value;
    }

    /**
     * @param $permission
     *
     * @return int|mixed|null
     */
    public function getPermissionValue($permission)
    {
        $permission = $this->findPermissionValue($permission);

        if (!$this->hasPermissionValue($permission)) {
            return $this->getDefaultValue($permission);
        }

        $role = $this->getRole();

        if (!$role instanceof Role) {
            return $this->getDefaultValue($permission);
        }

        $permissionWithPivot = $role->valuePermissions->first(function (Permission $row) use ($permission) {
            return $row->entityId() == $permission->entityId();
        });

        if (!$permissionWithPivot instanceof Permission) {
            return $this->getDefaultValue($permission);
        }

        $permissionValue = $permissionWithPivot->pivot->value;

        if ($permission->data_type === MetaFoxDataType::INTEGER) {
            $permissionValue = (int) $permissionValue;
        }

        return $permissionValue;
    }
}
