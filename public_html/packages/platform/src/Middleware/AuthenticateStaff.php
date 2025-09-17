<?php

namespace MetaFox\Platform\Middleware;

use MetaFox\Platform\Contracts\User;

/**
 * Class AuthenticateAdminCP.
 */
class AuthenticateStaff extends AuthenticateAdminCP
{
    /**
     * @param User|null $user
     *
     * @return bool
     */
    protected function hasAccess(?User $user): bool
    {
        if ($user?->hasPermissionTo('admincp.has_system_access')){
            return true;
        }

        return (bool)$user?->hasPermissionTo('admincp.has_admin_access');
    }
}
