<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Like\Models\Reaction as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ReactionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ReactionItem extends JsonResource
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
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->title,
            'icon'          => $this->resource->icon,
            'icon_mobile'   => $this->resource->icon_mobile,
            'icon_font'     => $this->resource->icon_font,
            'server_id'     => $this->resource->server_id,
            'color'         => "#{$this->resource->color}",
            'is_active'     => $this->isActive(),
            'ordering'      => $this->resource->ordering,
            'is_default'    => (bool)$this->resource->is_default,
            'total_item'    => $this->resource->total_item,
            'preview'       => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'links'         => [
                'editItem' => $this->resource->admin_edit_url,
            ],
        ];
    }

    public function isActive(): ?int
    {
        return !$this->resource->is_default ? $this->resource->is_active : null;
    }
}
