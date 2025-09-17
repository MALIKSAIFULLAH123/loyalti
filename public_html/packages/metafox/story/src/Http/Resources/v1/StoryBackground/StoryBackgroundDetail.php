<?php

namespace MetaFox\Story\Http\Resources\v1\StoryBackground;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Story\Models\StoryBackground as Model;

/**
 * Class StoryBackground.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StoryBackgroundDetail extends JsonResource
{
    public bool $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'story',
            'resource_name' => $this->resource->entityType(),
            'image'         => $this->resource->images,
            'ordering'      => $this->resource->ordering,
            'view_only'     => $this->resource->view_only,
            'is_deleted'    => $this->resource->is_deleted,
        ];
    }
}
