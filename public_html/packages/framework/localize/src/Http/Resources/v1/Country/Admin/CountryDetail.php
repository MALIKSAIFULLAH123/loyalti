<?php

namespace MetaFox\Localize\Http\Resources\v1\Country\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Localize\Models\Country as Model;

/**
 * Class CountryDetail.
 *
 * @property Model $resource
 */
class CountryDetail extends JsonResource
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
            'country_iso'   => $this->resource->country_iso,
            'name'          => $this->resource->name,
            'is_active'     => $this->resource->is_using ? null : $this->resource->is_active,
            'ordering'      => $this->resource->ordering,
            'url'           => sprintf('/localize/country/%s/state/browse', $this->resource->entityId()),
        ];
    }
}
