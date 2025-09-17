<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryChild\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Localize\Models\CountryChild as Model;

/**
 * Class CountryDetail.
 * @property Model $resource
 */
class CountryChildDetail extends JsonResource
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
            'state_iso'     => $this->resource->state_iso,
            'state_code'    => $this->resource->state_code,
            'geonames_code' => $this->resource->geonames_code,
            'fips_code'     => $this->resource->fips_code,
            'post_codes'    => $this->resource->post_codes,
            'timezone'      => $this->resource->timezone,
            'name'          => $this->resource->name,
            'ordering'      => $this->resource->ordering,
            'url'           => sprintf(
                '/localize/country/%d/state/%d/city/browse',
                $this->resource->country->entityId(),
                $this->resource->entityId()
            ),
        ];
    }
}
