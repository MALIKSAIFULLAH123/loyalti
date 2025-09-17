<?php

namespace MetaFox\Profile\Http\Resources\v1\Section\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Profile\Models\Section as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class SectionItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class SectionItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->name,
            'is_active'     => $this->is_system ? null : $this->is_active,
            'is_system'     => $this->is_system,
            'description'   => $this->description,
            'ordering'      => $this->ordering,
            'label'         => $this->resource->label,
            'links'         => [
                'editItem' => $this->resource->admin_edit_url,
            ],
        ];
    }
}
