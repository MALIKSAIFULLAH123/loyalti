<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\Category as Model;

/**
 * Class PageCategoryDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageCategoryDetail extends JsonResource
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
            'module_name'       => 'page',
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'is_active'         => $this->resource->is_active,
            'ordering'          => $this->resource->ordering,
            'url'               => $this->resource->toUrl(),
            'link'              => $this->resource->toLink(),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
