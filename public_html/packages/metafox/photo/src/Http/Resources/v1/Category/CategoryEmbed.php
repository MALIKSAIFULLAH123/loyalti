<?php

namespace MetaFox\Photo\Http\Resources\v1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Category;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;

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
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->id,
            'module_name'   => 'photo',
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
