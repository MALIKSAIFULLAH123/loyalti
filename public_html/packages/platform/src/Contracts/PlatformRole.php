<?php

namespace MetaFox\Platform\Contracts;

use MetaFox\Authorization\Models\Permission;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;

interface PlatformRole
{
    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     *
     * @throws GuardDoesNotMatch
     */
    public function hasPermissionValue($permission): bool;

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return mixed
     *
     * @throws GuardDoesNotMatch
     */
    public function getPermissionValue($permission);
}
