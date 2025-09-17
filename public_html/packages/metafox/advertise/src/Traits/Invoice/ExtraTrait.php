<?php

namespace MetaFox\Advertise\Traits\Invoice;

use MetaFox\Advertise\Models\Invoice;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission;

trait ExtraTrait
{
    public function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Invoice::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            'can_payment'                  => $policy->payment($context, $this->resource),
            'can_cancel'                   => $policy->cancel($context, $this->resource),
            ResourcePermission::CAN_DELETE => $policy->delete($context),
        ];
    }
}
