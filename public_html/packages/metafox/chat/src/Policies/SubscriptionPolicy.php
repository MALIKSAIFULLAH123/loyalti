<?php

namespace MetaFox\Chat\Policies;

use MetaFox\Platform\UserRole;

class SubscriptionPolicy
{
    public function migrateToChatPlus($context): bool
    {
        if (!$context->hasRole(UserRole::ADMIN_USER) && !$context->hasRole(UserRole::SUPER_ADMIN_USER)) {
            return false;
        }

        return true;
    }
}
