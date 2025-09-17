<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface UserRolePolicy
{
    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role);

    /**
     * @param User|null $user
     * @param string    $permission
     *
     * @return mixed
     */
    public function checkPermission(?User $user, string $permission);

    /**
     * @param User|null $user
     * @param array     $permissions
     *
     * @return mixed
     */
    public function hasAnyPermission(?User $user, array $permissions);

    public function hasAllPermission(?User $user, array $permissions);
}
