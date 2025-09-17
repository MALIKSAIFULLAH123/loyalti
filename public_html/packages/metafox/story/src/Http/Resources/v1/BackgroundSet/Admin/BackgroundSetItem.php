<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Story\Http\Resources\v1\BackgroundSet\BackgroundSetDetail;
use MetaFox\Story\Models\BackgroundSet as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class BackgroundSetItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class BackgroundSetItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $mainBackground = $this->resource->mainBackground;

        return [
            'id'                 => $this->resource->entityId(),
            'module_name'        => 'background-status',
            'resource_name'      => $this->resource->entityType(),
            'name'               => $this->resource->title,
            'is_active'          => $this->resource->is_active ? null : false,
            'preview'            => [
                'image'     => $this->resource->images,
                'file_type' => 'image/png',
                'url'       => null,
            ],
            'main_background_id' => $this->resource->main_background_id,
            'mainBackground'     => new BackgroundSetDetail($mainBackground),
            'view_only'          => $this->resource->view_only,
            'is_deleted'         => $this->resource->is_deleted,
            'total_background'   => $this->resource->total_background,
            'backgrounds'        => ResourceGate::items($this->resource->backgrounds, false),
            'links'              => [
                'editItem' => $this->resource->admin_edit_url,
            ],
        ];
    }
}
