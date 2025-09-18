<?php

namespace MetaFox\TourGuide\Http\Resources\v1\TourGuide\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\TourGuide\Models\TourGuide as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class TourGuideItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class TourGuideItem extends JsonResource
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
            'url'           => $this->resource->url,
            'is_active'     => $this->resource->is_active,
            'user'          => ResourceGate::user($this->resource->userEntity),
            'created_at'    => $this->resource->created_at,
        ];
    }
}
