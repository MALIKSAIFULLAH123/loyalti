<?php

namespace MetaFox\Group\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Category as Model;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class CategoryItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CategoryItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if (!$this->resource) {
            return [];
        }

        $parent = $this->resource?->parentCategory;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'group',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'subs'          => ResourceGate::items($this->resource->subCategories, false),
            'is_active'     => $this->resource->is_active,
            'total_item'    => $this->resource->total_item,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'ordering'      => $this->resource->ordering,
            'parent'        => $parent ? ResourceGate::embed($parent, false) : null,
        ];
    }
}
