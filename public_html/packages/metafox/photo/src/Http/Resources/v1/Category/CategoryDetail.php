<?php

namespace MetaFox\Photo\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Category as Model;

/**
 * Class CategoryDetail.
 * @property Model $resource
 */
class CategoryDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
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
