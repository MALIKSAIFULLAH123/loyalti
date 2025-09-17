<?php

namespace MetaFox\Platform\Http\Resources\v1\Category;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface;

/**
 * Class CategoryDetail.
 *
 * @property Model $resource
 */
abstract class AbstractCategoryDetail extends JsonResource
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
            'module_name'   => $this->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'name_url'      => $this->resource->name_url,
            'parent_id'     => $this->resource->parent_id,
            'is_active'     => $this->repository()->isActive($this->resource),
            'total_item'    => $this->resource->total_item,
            'ordering'      => $this->resource->ordering,
            'subs'          => ResourceGate::items($this->resource->subCategories, false),
        ];
    }

    abstract protected function moduleName(): string;

    abstract protected function repository(): CategoryRepositoryInterface;
}
