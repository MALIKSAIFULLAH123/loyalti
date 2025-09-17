<?php

namespace MetaFox\Subscription\Support\Browse\Traits\SubscriptionComparison;

use MetaFox\Platform\ResourcePermission;

trait ExtraTrait
{
    public function getExtra(): array
    {
        $context = user();

        $isAdmin = $context->hasPermissionTo('admincp.has_admin_access');

        return [
            ResourcePermission::CAN_EDIT   => $isAdmin,
            ResourcePermission::CAN_DELETE => $isAdmin,
        ];
    }
}
