<?php

namespace MetaFox\Story\Http\Resources\v1\Story\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\Settings;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class StoryServiceItem.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class StoryServiceItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'is_active'     => $this->resource->is_active,
            'module_name'   => 'story',
            'resource_name' => 'story',
            'name'          => __p($this->resource->title),
            'driver'        => $this->resource->driver,
            'detail_link'   => $this->resource->url,
            'is_default'    => Settings::get('story.video_service') == $this->resource->name,
        ];
    }
}
