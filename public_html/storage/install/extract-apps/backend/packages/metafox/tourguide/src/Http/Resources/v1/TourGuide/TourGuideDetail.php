<?php

namespace MetaFox\TourGuide\Http\Resources\v1\TourGuide;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\TourGuide\Models\TourGuide as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class TourGuideDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class TourGuideDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'tourguide',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'is_auto'       => $this->resource->is_auto,
            'steps'         => ResourceGate::items($this->resource->activeSteps, false),
        ];
    }
}
