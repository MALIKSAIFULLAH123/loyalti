<?php

namespace MetaFox\Group\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Category as Model;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;

/**
 * Class CategoryEmbed.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CategoryEmbed extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @TODO If this app requires a Core version lower than v5.14 and the current Core version more than v5.14 remove
     *        this method and extend class MetaFox\Platform\Http\Resources\v1\Category\AbstractCategoryDetail.
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
            'id'             => $this->resource->entityId(),
            'module_name'    => 'group',
            'resource_name'  => $this->resource->entityType(),
            'name'           => $this->resource->name,
            'url'            => $this->resource->toLink(),
            'level'          => $this->resource->level,
            'is_active'      => $this->repository()->isActive($this->resource),
            'parentCategory' => new $this($this->resource?->parentCategory),
        ];
    }

    /**
     * @return CategoryRepositoryInterface
     */
    public function repository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }
}
