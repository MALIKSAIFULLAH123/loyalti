<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;

class UserRoleDeletedListener
{
    public function handle(Entity $role, int $alternativeId)
    {
        resolve(SubscriptionPackageRepositoryInterface::class)->updateRoleId($role->entityId(), $alternativeId);
        resolve(SubscriptionPackageRepositoryInterface::class)->updateDowngradedUserRole($role->entityId());

        return null;
    }
}
