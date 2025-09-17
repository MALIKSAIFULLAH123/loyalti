<?php

namespace MetaFox\TourGuide\Http\Resources\v1\Step;

use MetaFox\TourGuide\Models\Step as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class StepDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class StepDetail extends StepItem
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'is_tour_guide_completed' => $this->resource->tourGuide->is_active,
        ]);
    }
}
