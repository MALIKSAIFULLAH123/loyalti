<?php

namespace MetaFox\App\Policies;

use MetaFox\App\Models\Package as Model;
use MetaFox\Core\Policies\Contracts\PackagePolicyInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class PackagePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PackagePolicy implements PackagePolicyInterface
{
    public function moderate(User $user): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function update(User $user, Model $package, array $params): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }
}
