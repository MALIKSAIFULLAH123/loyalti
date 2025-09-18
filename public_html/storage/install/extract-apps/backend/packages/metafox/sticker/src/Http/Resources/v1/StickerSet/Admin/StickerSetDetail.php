<?php

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Sticker\Models\StickerSet as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class StickerSetDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StickerSetDetail extends JsonResource
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
            'id'            => $this->resource->entityId(),
            'module_name'   => 'sticker',
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->title,
            'is_default'    => $this->resource->is_default,
            'is_active'     => $this->resource->is_active,
            'image'         => $this->resource->images,
            'ordering'      => $this->resource->ordering,
            'view_only'     => $this->resource->view_only,
            'is_deleted'    => $this->resource->is_deleted,
            'total_sticker' => $this->resource->total_sticker,
        ];
    }
}
