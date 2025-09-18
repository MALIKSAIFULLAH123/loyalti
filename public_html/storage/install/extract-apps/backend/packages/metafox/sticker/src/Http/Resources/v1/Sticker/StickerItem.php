<?php

namespace MetaFox\Sticker\Http\Resources\v1\Sticker;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Sticker\Models\Sticker as Model;

/**
 * Class StickerItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StickerItem extends JsonResource
{
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
            'module_name'   => 'sticker',
            'resource_name' => $this->resource->entityType(),
            'image'         => $this->resource->image,
            'thumbs'        => $this->resource->thumbs,
            'ordering'      => $this->resource->ordering,
            'view_only'     => $this->resource->view_only,
            'is_deleted'    => $this->resource->is_deleted,
        ];
    }
}
