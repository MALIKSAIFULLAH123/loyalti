<?php

namespace MetaFox\User\Listeners;

use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\User\Repositories\UserBanRepositoryInterface;

class UserRoleDeletedListener
{
    public function handle(Entity $role, int $alternativeId)
    {
        resolve(RoleRepositoryInterface::class)->updateRegisteredRoleSetting($role->entityId());
        resolve(UserBanRepositoryInterface::class)->updateAlternativeRoleId($role->entityId(), $alternativeId);
    }
}
