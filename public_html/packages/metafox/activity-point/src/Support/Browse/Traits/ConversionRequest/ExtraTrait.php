<?php
namespace MetaFox\ActivityPoint\Support\Browse\Traits\ConversionRequest;

use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Policies\ConversionRequestPolicy;

/**
 * @property ConversionRequest $resource
 */
trait ExtraTrait
{
    public function getExtra(): array
    {
        /**
         * @var ConversionRequestPolicy $policy
         */
        $policy = PolicyGate::getPolicyFor(ConversionRequest::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            'can_cancel' => $policy->cancelConversionRequest($context, $this->resource),
            'can_view_reason' => $policy->viewDeniedReason($context, $this->resource),
        ];
    }
}
