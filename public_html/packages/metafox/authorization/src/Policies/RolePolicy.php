<?php

namespace MetaFox\Authorization\Policies;

use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Models\User as Model;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class RolePolicy
{
    use HasPolicyTrait;

    protected string $type = Role::ENTITY_TYPE;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('user_role.manage');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermissionTo('user_role.manage');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('user_role.manage');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Role $role
     *
     * @return bool
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('user_role.manage');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Role $role
     *
     * @return bool
     */
    public function delete(User $user, Role $role): bool
    {
        if ($role->is_special == 1) {
            return false;
        }

        return $user->hasPermissionTo('user_role.manage');
    }

    public function inheritFromParent(User $user, Role $parentRole): bool
    {
        // All custom role shall not be in this scope because it is checked at the time of creation
        if ($parentRole->is_custom) {
            return true;
        }

        // The action can be proceeded when user already has the target role
        if ($user->hasRole($parentRole)) {
            return true;
        }

        // The action can be proceeded only when:
        // The smallest role id which user has must be larger or equal the target role id
        // due to the priority order of the roles
        if ($user->getSmallestRoleId() <= $parentRole->entityId()) {
            return true;
        }

        return false;
    }
}
