<?php

namespace MetaFox\Newsletter\Support\Browse\Traits;

use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission;

trait ExtraTrait
{
    public function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Newsletter::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            ResourcePermission::CAN_EDIT   => $policy->update($context, $this->resource),
            ResourcePermission::CAN_DELETE => $policy->delete($context, $this->resource),
            'can_process'                  => $policy->process($this->resource),
            'can_stop'                     => $policy->stop($this->resource),
            'can_reprocess'                => $policy->reprocess($this->resource),
            'can_resend'                   => $policy->resend($this->resource),
        ];
    }
}
