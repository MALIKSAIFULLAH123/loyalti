<?php

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Sticker\Models\StickerSet as Model;

/**
 * Class StickerSetDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StickerSetDetail extends JsonResource
{
    use HasStatistic;

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'used'          => 0,
            'total_sticker' => $this->resource->total_sticker,
        ];
    }

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
            'title'         => $this->resource->title,
            'is_default'    => $this->resource->is_default,
            'is_active'     => $this->resource->is_active,
            'thumbnail_id'  => $this->resource->thumbnail_id,
            'image'         => $this->resource->images,
            'thumbs'        => $this->resource->thumbnail->thumbs,
            'ordering'      => $this->resource->ordering,
            'view_only'     => $this->resource->view_only,
            'is_deleted'    => $this->resource->is_deleted,
            'total_sticker' => $this->resource->total_sticker,
        ];
    }
}
