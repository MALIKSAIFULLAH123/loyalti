<?php

namespace MetaFox\Event\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Models\Category as Model;
use MetaFox\Event\Models\Event;

/**
 * Class CategoryDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CategoryDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request       $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => Event::ENTITY_TYPE,
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'name_url'      => $this->resource->name_url,
            'parent_id'     => $this->resource->parent_id,
            'is_active'     => $this->resource->is_active,
            'total_item'    => $this->resource->total_item,
            'ordering'      => $this->resource->ordering,
            'subs'          => new CategoryItemCollection($this->resource->subCategories),
        ];
    }
}
