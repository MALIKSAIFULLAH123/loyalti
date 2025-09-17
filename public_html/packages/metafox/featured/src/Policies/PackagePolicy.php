<?php

namespace MetaFox\Featured\Policies;

use MetaFox\Featured\Models\Package;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/policies/model_policy.stub
 */

/**
 * Class PackagePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PackagePolicy
{
    protected function hasAdmincpAccess(User $user): bool
    {
        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->hasAdmincpAccess($user);
    }

    public function edit(User $user, Package $package): bool
    {
        return $this->hasAdmincpAccess($user);
    }

    public function delete(User $user, Package $package): bool
    {
        return $this->hasAdmincpAccess($user);
    }
}
