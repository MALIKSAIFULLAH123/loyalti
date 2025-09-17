<?php

namespace MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\StaticPage\Models\StaticPage as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class StaticPageDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class StaticPageDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $content = $this->resource->content ?? $this->resource->masterContent;

        return [
            'id'    => $this->id,
            'slug'  => $this->resource->slug,
            'title' => $this->resource->title,
            'text'  => parse_output()->parseUrl($content?->text),
        ];
    }
}
