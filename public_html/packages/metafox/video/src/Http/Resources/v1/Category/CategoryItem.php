<?php

namespace MetaFox\Video\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;

/**
 * Class CategoryItem.
 *
 * @property Category $resource
 */
class CategoryItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @TODO If this app requires a Core version lower than v5.14 and the current Core version more than v5.14 remove
     *       this method and extend class MetaFox\Platform\Http\Resources\v1\Category\AbstractCategoryDetail.
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
            'is_active'     => $this->repository()->isActive($this->resource),
            'module_name'   => 'video',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'name_url'      => $this->resource->name_url,
            'total_item'    => $this->resource->total_item,
            'ordering'      => $this->resource->ordering,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'subs'          => new CategoryItemCollection($this->resource->subCategories),
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
