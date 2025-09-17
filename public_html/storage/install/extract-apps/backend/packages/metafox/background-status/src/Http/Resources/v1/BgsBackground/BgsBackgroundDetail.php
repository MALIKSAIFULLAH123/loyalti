<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\BackgroundStatus\Models\BgsBackground as Model;

/**
 * Class BgsBackgroundDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BgsBackgroundDetail extends JsonResource
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
            'module_name'   => 'background-status',
            'resource_name' => $this->resource->entityType(),
            'image'         => $this->resource->images,
            'ordering'      => $this->resource->ordering,
            'view_only'     => $this->resource->view_only,
            'text_color'    => $this->resource->text_color,
            'is_deleted'    => $this->resource->is_deleted,
        ];
    }
}
