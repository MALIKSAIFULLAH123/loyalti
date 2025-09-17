<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory\Admin;

use Illuminate\Http\Request;
use MetaFox\Page\Models\Category;

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
            'is_default'    => $this->resource->is_default,
            'is_active'     => $this->isActive(),
            'module_name'   => 'page',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'name_url'      => $this->resource->name_url,
            'total_item'    => $this->resource->total_item,
            'ordering'      => $this->resource->ordering,
        ];
    }
}
