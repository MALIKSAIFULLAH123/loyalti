<?php

namespace MetaFox\Blog\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Blog\Models\Category;
use MetaFox\Blog\Repositories\CategoryRepositoryInterface;

/*
|--------------------------------------------------------------------------
| Resource Embed
|--------------------------------------------------------------------------
|
| Resource embed is used when you want attach this resource as embed content of
| activity feed, notification, ....
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
*/

/**
 * Class CategoryEmbed.
 *
 * @property Category $resource
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
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'module_name'   => 'blog',
            'resource_name' => $this->resource->entityType(),
            'is_active'     => $this->repository()->isActive($this->resource),
            'name'          => $this->resource->name,
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
