<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Story\Http\Resources\v1\StoryBackground\StoryBackgroundDetail;
use MetaFox\Story\Models\BackgroundSet as Model;

/**
 * Class BackgroundSetEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BackgroundSetEmbed extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'                 => $this->resource->entityId(),
            'module_name'        => 'background-status',
            'resource_name'      => $this->resource->entityType(),
            'is_default'         => $this->resource->is_default,
            'is_active'          => $this->resource->is_active,
            'main_background_id' => $this->resource->main_background_id,
            'mainBackground'     => new StoryBackgroundDetail($this->resource->mainBackground),
            'view_only'          => $this->resource->view_only,
            'is_deleted'         => $this->resource->is_deleted,
            'total_background'   => $this->resource->total_background,
        ];
    }
}
