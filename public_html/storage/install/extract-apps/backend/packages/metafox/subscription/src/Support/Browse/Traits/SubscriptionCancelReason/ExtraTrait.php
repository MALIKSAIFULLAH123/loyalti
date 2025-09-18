<?php

namespace MetaFox\Subscription\Support\Browse\Traits\SubscriptionCancelReason;

use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission;
use MetaFox\Subscription\Models\SubscriptionCancelReason;
use MetaFox\Subscription\Support\Helper;

trait ExtraTrait
{
    public function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(SubscriptionCancelReason::class);

        $context = user();

        $isAdmin = $context->hasPermissionTo('admincp.has_admin_access');

        return [
            ResourcePermission::CAN_EDIT   => $isAdmin,
            Helper::PERMISSION_CAN_ACTIVE  => $isAdmin,
            ResourcePermission::CAN_DELETE => $policy->delete($context, $this->resource),
        ];
    }
}
