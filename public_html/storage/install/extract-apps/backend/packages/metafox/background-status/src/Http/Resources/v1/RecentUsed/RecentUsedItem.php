<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\RecentUsed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\BackgroundStatus\Models\RecentUsed as Model;

/**
 * Class RecentUsedItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class RecentUsedItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request       $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => '',
            'resource_name' => $this->resource->entityType(),
        ];
    }
}
