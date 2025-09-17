<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\ActivityPoint\Models\ConversionRequest as Model;
use MetaFox\ActivityPoint\Policies\ConversionRequestPolicy;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ConversionRequestItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ConversionRequestItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'activitypoint',
            'resource_name'     => $this->resource->entityType(),
            'user'              => ResourceGate::transactionUser($this->resource->userEntity),
            'points'            => number_format($this->resource->points),
            'status'            => $this->resource->status_text,
            'total'             => $this->resource->total_text,
            'fee'               => $this->resource->commission_text,
            'actual'            => $this->resource->actual_text,
            'reason'            => $this->resource->denied_reason,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'extra'             => $this->getExtra(),
        ];
    }

    protected function getExtra(): array
    {
        /**
         * @var ConversionRequestPolicy $policy
         */
        $policy = PolicyGate::getPolicyFor(Model::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            'can_deny' => $policy->denyConversionRequest($context, $this->resource),
            'can_approve' => $policy->approveConversionRequest($context, $this->resource),
            'can_view_reason' => $policy->viewDeniedReason($context, $this->resource),
        ];
    }
}
