<?php

namespace MetaFox\Advertise\Traits\Advertise;

use MetaFox\Advertise\Models\Advertise;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission;

trait ExtraTrait
{
    public function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Advertise::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            ResourcePermission::CAN_EDIT   => $policy->update($context, $this->resource),
            ResourcePermission::CAN_DELETE => $policy->delete($context, $this->resource),
            'can_payment'                  => $policy->payment($context, $this->resource),
            'can_mark_as_paid'             => $policy->markAsPaid($context, $this->resource),
            'can_export'                   => false,
            'can_hide'                     => $policy->hide($context, $this->resource),
            'can_active'                   => $policy->active($context, $this->resource),
        ];
    }
}
