<?php

namespace MetaFox\Video\Http\Resources\v1\Category\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Video\Models\Category;

/**
 * Class CategoryItem.
 * @property Category $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryEmbed extends CategoryItem
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'is_active'     => $this->isActive(),
            'is_default'    => $this->resource->is_default,
            'module_name'   => 'video',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'name_url'      => $this->resource->name_url,
            'total_item'    => $this->resource->total_item,
            'ordering'      => $this->resource->ordering,
        ];
    }
}
