<?php

namespace MetaFox\Video\Http\Resources\v1\VideoService\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\Settings;

/**
 * Class VideoServiceItem.
 */
class VideoServiceItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
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
            'is_active'     => $this->resource->is_active,
            'module_name'   => 'video',
            'resource_name' => 'video_service',
            'name'          => __p($this->resource->title),
            'driver'        => $this->resource->driver,
            'detail_link'   => $this->resource->url,
            'is_default'    => Settings::get('video.video_service') == $this->resource->name,
        ];
    }
}
