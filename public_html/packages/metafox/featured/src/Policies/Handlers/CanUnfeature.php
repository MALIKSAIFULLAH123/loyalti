<?php
namespace MetaFox\Featured\Policies\Handlers;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanUnfeature implements PolicyRuleInterface
{

    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->isDraft()) {
            return false;
        }

        if (!$resource->is_featured) {
            return false;
        }

        if ($user->hasPermissionTo("$entityType.feature")) {
            return true;
        }

        if (!$user->hasPermissionTo("$entityType.purchase_feature")) {
            return false;
        }

        if (Feature::isFeaturedByUser($user, $resource)) {
            return true;
        }

        return false;
    }
}
