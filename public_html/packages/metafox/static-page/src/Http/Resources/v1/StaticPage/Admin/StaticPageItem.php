<?php

namespace MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\StaticPage\Models\StaticPage as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class StaticPageItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class StaticPageItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'slug'  => $this->resource->slug,
            'title' => $this->resource->title,
            'link'  => $this->resource->toLink(),
            'url'   => $this->resource->toUrl(),
        ];
    }
}
