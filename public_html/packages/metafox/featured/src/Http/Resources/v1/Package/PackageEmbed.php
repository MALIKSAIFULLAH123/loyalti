<?php
namespace MetaFox\Featured\Http\Resources\v1\Package;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Featured\Models\Package;

/**
 * @property Package $resource
 */
class PackageEmbed extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->entityId(),
            'module_name' => 'featured',
            'resource_name' => $this->resource->entityType(),
            'title' => $this->resource->toTitle(),
        ];
    }
}
