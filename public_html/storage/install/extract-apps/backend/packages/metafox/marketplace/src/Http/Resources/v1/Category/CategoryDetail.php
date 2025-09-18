<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Marketplace\Models\Category as Model;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

/**
 * Class CategoryDetail.
 * @property Model $resource
 */
class CategoryDetail extends JsonResource
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
            'module_name'   => 'marketplace',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'parent_id'     => $this->resource->parent_id,
            'total_item'    => $this->resource->total_item,
            'ordering'      => $this->resource->ordering,
            'is_active'     => $this->resource->is_active,
            'creation_date' => $this->resource->created_at,
            'subs'          => new CategoryItemCollection($this->resource->subCategories),
            'url'           => $this->resource->toUrl(),
            'link'          => $this->resource->toLink(),
        ];
    }
}
