<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\Category as Model;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;

/**
 * Class PageCategoryEmbed.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageCategoryEmbed extends JsonResource
{
    /**
     * @TODO : If this app requires a Core version lower than v5.14 and the current Core version more than v5.14 remove
     * @TODO this method and extend class MetaFox\Platform\Http\Resources\v1\Category\AbstractCategoryEmbed. Transform
     * the resource collection into an array.
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
            'module_name'    => 'page',
            'resource_name'  => $this->resource->entityType(),
            'name'           => $this->resource->name,
            'url'            => $this->resource->toUrl(),
            'link'           => $this->resource->toLink(),
            'level'          => $this->resource->level,
            'is_active'      => $this->repository()->isActive($this->resource),
            'parentCategory' => new $this($this->resource?->parentCategory),
        ];
    }

    /**
     * @return PageCategoryRepositoryInterface
     */
    public function repository(): PageCategoryRepositoryInterface
    {
        return resolve(PageCategoryRepositoryInterface::class);
    }
}
