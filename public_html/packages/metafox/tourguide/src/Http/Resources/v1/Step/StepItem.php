<?php

namespace MetaFox\TourGuide\Http\Resources\v1\Step;

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
            'id'               => $this->resource->entityId(),
            'module_name'      => 'tourguide',
            'resource_name'    => $this->resource->entityType(),
            'title'            => $this->resource->title,
            'desc'             => $this->resource->desc,
            'ordering'         => $this->resource->ordering,
            'delay'            => $this->resource->delay,
            'background_color' => $this->resource->background_color,
            'font_color'       => $this->resource->font_color,
            'element'          => $this->resource->element,
        ];
    }
}
