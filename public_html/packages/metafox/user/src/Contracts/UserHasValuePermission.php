<?php

namespace MetaFox\User\Contracts;

use MetaFox\Authorization\Models\Permission;

/**
 * Interface UserHasValuePermission.
 * @ignore
 */
interface UserHasValuePermission
{
    /**
     * @param Permission $permission
     *
     * @return bool
     */
    public function hasValuePermissionViaRole(Permission $permission): bool;
}
