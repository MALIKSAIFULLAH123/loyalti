<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Story\Models\BackgroundSet as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class BackgroundSetItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class BackgroundSetItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $mainBackground = $this->resource->mainBackground;

        return [
            'id'               => $this->resource->entityId(),
            'module_name'      => 'story',
            'resource_name'    => $this->resource->entityType(),
            'is_default'       => $this->resource->is_default,
            'is_active'        => $this->resource->is_active,
            'name'             => $this->resource->title,
            'image'            => $mainBackground?->images,
            'view_only'        => $this->resource->view_only,
            'is_deleted'       => $this->resource->is_deleted,
            'total_background' => $this->resource->total_background,
            'backgrounds'      => ResourceGate::items($this->resource->backgrounds, false),
        ];
    }
}
