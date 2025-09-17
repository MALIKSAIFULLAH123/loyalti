<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Policy;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User as User;

/**
 * Trait HasCategoryPolicyTrait.
 */
trait HasCategoryPolicyTrait
{
    public function viewActive(User $user, ?Entity $category): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        return (bool) $category?->is_active;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }
}
