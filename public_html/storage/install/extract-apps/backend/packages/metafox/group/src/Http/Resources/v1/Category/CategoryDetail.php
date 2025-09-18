<?php

namespace MetaFox\Group\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Category as Model;

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
            'module_name'       => 'group',
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'parent_id'         => $this->resource->parent_id,
            'is_active'         => $this->resource->is_active,
            'ordering'          => $this->resource->ordering,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'total_item'        => $this->resource->total_item,
            'url'               => $this->resource->toUrl(),
        ];
    }
}
