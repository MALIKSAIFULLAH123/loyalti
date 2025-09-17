<?php

namespace MetaFox\Featured\Policies\Handlers;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanFeatureFree extends BaseFeatureHandler implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$this->validateResourceStatus($resource)) {
            return false;
        }

        if (!$user->hasPermissionTo("$entityType.feature")) {
            return false;
        }

        if (!$this->validatePermissionOnResource($user, $resource)) {
            return false;
        }

        if (null === $resource->toFeaturedData()) {
            return false;
        }

        if (!Feature::isContentAvailableForFeature($resource)) {
            return false;
        }

        return true;
    }
}
