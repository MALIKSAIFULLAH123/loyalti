<?php

namespace MetaFox\TourGuide\Http\Resources\v1\Step\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\TourGuide\Models\Step as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class StepItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class StepItem extends JsonResource
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
            'title'         => $this->resource->title,
            'desc'          => parse_output()->getDescription($this->resource->desc),
            'ordering'      => $this->resource->ordering,
            'is_active'     => $this->resource->is_active,
        ];
    }
}
