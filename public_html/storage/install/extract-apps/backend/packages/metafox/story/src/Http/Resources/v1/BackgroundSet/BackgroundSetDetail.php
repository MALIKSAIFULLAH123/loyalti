<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Story\Models\BackgroundSet as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class BackgroundSetDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class BackgroundSetDetail extends JsonResource
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
            'module_name'   => 'story',
            'resource_name' => $this->resource->entityType(),
            'image'         => $this->resource->images,
            'ordering'      => $this->resource->ordering,
            'view_only'     => $this->resource->view_only,
            'is_deleted'    => $this->resource->is_deleted,
        ];
    }
}
