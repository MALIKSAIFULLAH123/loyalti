<?php

namespace MetaFox\Storage\Http\Resources\v1\Disk\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Storage\Models\Disk as Model;

/**
 * @property Model $resource
 */
class DiskItem extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->resource->id,
            'name'       => $this->resource->name,
            'label'      => $this->resource->label,
            'target'     => $this->resource->target,
            'title'      => $this->resource->title,
            'can_edit'   => true,
            'can_delete' => !$this->resource->is_system,
            'links'      => [
                'editItem' => sprintf('/storage/disk/edit/%s', $this->resource->id),
            ],
        ];
    }
}
