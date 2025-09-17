<?php

namespace MetaFox\StaticPage\Http\Resources\v1\StaticPage;

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
            'id'            => $this->id,
            'module_name'   => 'static-page',
            'resource_name' => 'static_page',
            'text'          => parse_output()->parseUrl($content?->text),
            'title'         => $this->resource->title,
            'created_at'    => $this->resource->created_at,
            'modified_at'   => $this->resource->modified_at,
        ];
    }
}
