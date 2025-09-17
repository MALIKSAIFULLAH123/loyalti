<?php

namespace MetaFox\Layout\Http\Resources\v1\Variant\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Layout\Models\Variant as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * class VariantItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class VariantItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $theme = $this->theme;

        $default  = $this->is_default;

        return [
            'id'          => $this->id,
            'theme_id'    => $this->theme_id,
            'theme_title' => $theme?->title ?? $this->theme_id,
            'variant_id'  => $this->variant_id,
            'title'       => $this->title,
            'is_active'   => $this->is_default ? null : $this->is_active,
            'is_default'  => $default ? null : $this->is_default,
            'is_system'   => $this->is_system,
            'can_delete'  => !$this->is_default && !$this->is_system,
            'preview'     => [
                'url'       => $this->resource->imageUrl,
                'file_type' => 'image/png',
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'links'      => [
                'createVariant' => '/layout/variant/create?theme_id=' . $this->theme_id,
                'viewTheme'     => '/layout/theme/browse',
            ],
        ];
    }
}
