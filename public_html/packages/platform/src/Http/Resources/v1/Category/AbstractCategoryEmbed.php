<?php

namespace MetaFox\Platform\Http\Resources\v1\Category;

use Illuminate\Http\Request;

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
 */
abstract class AbstractCategoryEmbed extends AbstractCategoryDetail
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'module_name'   => $this->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'is_active'     => $this->repository()->isActive($this->resource),
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
        ];
    }
}
