<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\StreamingService\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\LiveStreaming\Models\StreamingService as Model;

/**
 * Class VideoServiceItem.
 * @property Model $resource
 */
class StreamingServiceItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'is_active'     => $this->resource->is_active,
            'module_name'   => 'livestreaming',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'driver'        => $this->resource->service_class,
            'detail_link'   => $this->resource->detail_link,
        ];
    }
}
