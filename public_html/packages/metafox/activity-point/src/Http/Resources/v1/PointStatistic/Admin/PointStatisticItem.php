<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ActivityPoint\Models\PointStatistic as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PointStatisticItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PointStatisticItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->resource->userEntity?->entityId(),
            'name'            => $this->resource->userEntity?->display_name,
            'current_points'  => $this->resource->current_points,
            'total_earned'    => $this->resource->total_earned,
            'total_bought'    => $this->resource->total_bought,
            'total_sent'      => $this->resource->total_sent,
            'total_spent'     => $this->resource->total_spent,
            'total_received'  => $this->resource->total_received,
            'total_retrieved' => $this->resource->total_retrieved,
            'link'            => $this->resource->userEntity?->toUrl(),
        ];
    }
}
