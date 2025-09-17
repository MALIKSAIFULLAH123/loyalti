<?php

namespace MetaFox\Forum\Support\Browse\Traits\Moderate;

use MetaFox\Forum\Repositories\UserRolePermissionRepositoryInterface;

trait UserRolePermissionTrait
{
    public function hasUserRolePermissionAccess(int $userRoleId, int|null $forumId, string $permission): bool
    {
        return resolve(UserRolePermissionRepositoryInterface::class)->hasAccess($userRoleId, $forumId, $permission);
    }
}
