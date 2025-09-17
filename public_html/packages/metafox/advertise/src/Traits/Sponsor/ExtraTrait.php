<?php

namespace MetaFox\Advertise\Traits\Sponsor;

use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission;

/**
 * @property Sponsor $resource
 */
trait ExtraTrait
{
    public function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Sponsor::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            ResourcePermission::CAN_EDIT   => $policy->update($context, $this->resource),
            ResourcePermission::CAN_DELETE => $policy->delete($context, $this->resource),
            'can_payment'                  => $policy->payment($context, $this->resource),
            'can_mark_as_paid'             => $policy->markAsPaid($context, $this->resource),
            'can_active'                   => $policy->active($context, $this->resource),
        ];
    }
}
