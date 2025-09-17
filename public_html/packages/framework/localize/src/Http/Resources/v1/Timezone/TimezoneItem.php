<?php

namespace MetaFox\Localize\Http\Resources\v1\Timezone;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Localize\Models\Timezone as Model;

/**
 * Class TimezoneItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TimezoneItem extends JsonResource
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
            'id'            => $this->resource->entityId(),
            'module_name'   => 'core',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'offset'        => $this->resource->offset,
            'diff_from_gtm' => $this->resource->diff_from_gtm,
            'is_active'     => $this->resource->is_active,
        ];
    }
}
