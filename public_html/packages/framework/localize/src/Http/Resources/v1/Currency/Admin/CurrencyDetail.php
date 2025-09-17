<?php

namespace MetaFox\Localize\Http\Resources\v1\Currency\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Localize\Models\Currency as Model;

/**
 * Class CurrencyDetail.
 *
 * @property Model $resource
 */
class CurrencyDetail extends JsonResource
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
            'module_name'   => 'core',
            'resource_name' => $this->resource->entityType(),
            'code'          => $this->resource->code,
            'symbol'        => $this->resource->symbol,
            'name'          => $this->resource->name,
            'format'        => $this->resource->format,
            'is_active'     => $this->resource->isActive(),
            'is_default'    => $this->resource->is_default ? null : false,
            'ordering'      => $this->resource->ordering,
        ];
    }
}
