<?php

namespace MetaFox\Photo\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Category;

/**
 * Class CategoryItem.
 * @property Category $resource
 */
class CategoryItem extends JsonResource
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
        $parent = null;

        if ($this->resource->parent_id) {
            $parent = new CategoryEmbed($this->resource->parentCategory);
        }

        return [
            'id'            => $this->resource->entityId(),
            'is_active'     => $this->resource->is_active,
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'name_url'      => $this->resource->name_url,
            'total_item'    => $this->resource->total_item,
            'ordering'      => $this->resource->ordering,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'subs'          => new CategoryItemCollection($this->resource->subCategories),
            'parent'        => $parent,
        ];
    }
}
