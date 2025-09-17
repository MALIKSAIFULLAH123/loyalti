<?php

namespace MetaFox\Authorization\Policies;

use MetaFox\Authorization\Models\Permission;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\Platform\UserRole;

/**
 * Class PermissionPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PermissionPolicy
{
    use HasPolicyTrait;

    protected string $type = Permission::class;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('user_permission.manage');
    }

    /**
     * Determine whether the user can view a model.
     *
     * @param User $user
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermissionTo('user_permission.manage');
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if (!$user->hasPermissionTo('user_permission.manage')) {
            return false;
        }

        if ($user->hasRole(UserRole::NORMAL_USER) || $user->hasRole(UserRole::STAFF_USER)) {
            return false;
        }

        if (!$resource instanceof Permission) {
            return false;
        }

        if ($resource->require_admin) {
            return $user->hasSuperAdminRole();
        }

        return true;
    }
}
