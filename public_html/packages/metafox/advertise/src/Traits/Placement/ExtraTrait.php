<?php

namespace MetaFox\Advertise\Traits\Placement;

use MetaFox\Advertise\Models\Placement;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission;

trait ExtraTrait
{
    public function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Placement::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            ResourcePermission::CAN_EDIT   => call_user_func([$policy, 'update'], $context, $this->resource),
            ResourcePermission::CAN_DELETE => call_user_func([$policy, 'delete'], $context, $this->resource),
        ];
    }
}
