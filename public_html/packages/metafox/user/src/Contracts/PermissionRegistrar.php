<?php

namespace MetaFox\User\Contracts;

use MetaFox\Platform\Contracts\User;

/**
 * Interface PermissionRegistrar.
 */
interface PermissionRegistrar
{
    /**
     * @param User   $user
     * @param string $permission
     *
     * @return bool|null
     */
    public function getPermissionViaRole(User $user, string $permission): ?bool;

    /**
     * @param User   $user
     * @param string $permission
     * @param bool   $value
     */
    public function setPermissionViaRole(User $user, string $permission, bool $value): void;
}
