<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\Category as Model;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class PageCategoryItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageCategoryItem extends JsonResource
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

        return [
            'id'                => $this->resource->entityId(),
            'is_active'         => $this->resource->is_active,
            'module_name'       => 'page',
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'ordering'          => $this->resource->ordering,
            'creation_date'     => $this->resource->created_at,
            'subs'              => ResourceGate::items($this->resource->subCategories, false),
            'modification_date' => $this->resource->updated_at,
            'total_item'        => $this->resource->total_item,
            'url'               => $this->resource->toUrl(),
            'link'              => $this->resource->toLink(),
            'parent'            => ResourceGate::embed($this->resource?->parentCategory),
        ];
    }
}
