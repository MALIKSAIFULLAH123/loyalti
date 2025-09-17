<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Policies\SharePolicy;
use MetaFox\Platform\Contracts\User;

class CanShareListener
{
    public function handle(string $entityType, User $user, $resource, $newValue = null): bool
    {
        if ($entityType === Share::ENTITY_TYPE && $resource instanceof Share) {
            if (null === $resource->item) {
                return false;
            }

            $packageId = app('events')->dispatch('core.driver.get_package_id_by_entity', [$resource->item->entityType()], true);

            if (is_string($packageId) && !app_active($packageId)) {
                return false;
            }
        }

        return policy_check(SharePolicy::class, 'share', $entityType, $user, $resource, $newValue);
    }
}
