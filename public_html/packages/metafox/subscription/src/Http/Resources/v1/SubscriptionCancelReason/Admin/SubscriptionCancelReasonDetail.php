<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use MetaFox\Subscription\Support\Browse\Traits\SubscriptionCancelReason\ExtraTrait;
use MetaFox\Subscription\Support\Browse\Traits\SubscriptionCancelReason\StatisticTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class SubscriptionCancelReasonDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class SubscriptionCancelReasonDetail extends JsonResource
{
    use ExtraTrait;
    use StatisticTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $resource = $this->resource;

        $title = $resource->toTitle();

        if ($resource->is_default) {
            $title =  '(' . __p('core::web.default_ucfirst') . ') ' . $title;
        }

        return [
            'id'            => $resource->entityId(),
            'module_name'   => 'subscription',
            'resource_name' => $resource->entityType(),
            'title'         => $title,
            'is_active'     => $resource->is_default ? null : $resource->is_active,
            'is_default'    => $resource->is_default,
            'statistic'     => $this->getStatistics(),
            'extra'         => $this->getExtra(),
        ];
    }
}
