<?php

namespace MetaFox\Featured\Http\Resources\v1\Package;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Featured\Models\Package as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PackageItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PackageItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->entityId(),
            'module_name' => 'featured',
            'resource_name' => $this->resource->entityType(),
            'name' => $this->resource->toTitle(),
        ];
    }
}
