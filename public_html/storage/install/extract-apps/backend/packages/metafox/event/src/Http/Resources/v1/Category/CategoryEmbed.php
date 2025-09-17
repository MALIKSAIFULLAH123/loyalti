<?php

namespace MetaFox\Event\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Models\Category as Model;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;

/**
 * Class CategoryItem.
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
     *        this method and extend class MetaFox\Platform\Http\Resources\v1\Category\AbstractCategoryEmbed.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => Event::ENTITY_TYPE,
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'is_active'     => $this->repository()->isActive($this->resource),
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
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
