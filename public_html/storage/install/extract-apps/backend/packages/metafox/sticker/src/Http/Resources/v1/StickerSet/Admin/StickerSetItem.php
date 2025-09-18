<?php

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Sticker\Models\StickerSet as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class StickerSetItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class StickerSetItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->title,
            'is_default'    => $this->resource->is_default,
            'is_active'     => $this->resource->is_active,
            'view_only'     => (bool)$this->resource->view_only,
            'total_sticker' => $this->resource->total_sticker,
            'avatar'        => $this->resource->avatar,
            'links'         => [
                'editItem' => $this->resource->admin_edit_url,
            ],
        ];
    }
}
