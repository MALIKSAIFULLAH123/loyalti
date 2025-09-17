<?php

namespace MetaFox\Advertise\Http\Resources\v1\Placement;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Placement as Model;
use MetaFox\Advertise\Traits\Placement\ExtraTrait;
use MetaFox\Advertise\Traits\Placement\StatisticTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class PlacementEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class PlacementEmbed extends JsonResource
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
        return [
            'id'             => $this->resource->entityId(),
            'module_name'    => 'advertise',
            'resource_name'  => $this->resource->entityType(),
            'title'          => $this->resource->toTitle(),
            'is_active'      => $this->resource->is_active,
            'placement_type' => $this->resource->placement_type,
            'extra'          => $this->getExtra(),
            'statistic'      => $this->getStatistics(),
        ];
    }
}
