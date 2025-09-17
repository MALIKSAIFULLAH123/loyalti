<?php

namespace MetaFox\Authorization\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use MetaFox\User\Contracts\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles;

    private ?PermissionRegistrar $permissionRegistrar = null;

    public function getPermissionRegistrar(): PermissionRegistrar
    {
        if (!isset($this->permissionRegistrar)) {
            $this->permissionRegistrar = app(PermissionRegistrar::class);
        }

        return $this->permissionRegistrar;
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (!is_string($permission)) {
            abort(500, 'MetaFox does not support permission not STRING');
        }

        return $this->getPermissionRegistrar()->getPermissionViaRole($this, $permission);
    }

    public function roleId(): int
    {
        return $this->getRole()?->id ?? 0;
    }

    protected function resolvePermissionModel(string $permission, $guardName = null)
    {
        $guardName = $guardName ?? 'api';

        try {
            return $this->getPermissionClass()->findByName($permission, $guardName);
        } catch (Exception) {
            // try to resolve wildcard permission
            $result =   $this->getPermissionClass()->findByWildcardName($permission, $guardName);

            return $result;
        }
    }
}
