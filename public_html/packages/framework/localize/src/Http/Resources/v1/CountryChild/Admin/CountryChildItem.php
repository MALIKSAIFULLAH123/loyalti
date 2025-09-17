<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryChild\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Localize\Models\CountryChild as Model;

/**
 * Class CountryChildItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CountryChildItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'name'          => $this->resource->name,
            'geonames_code' => $this->resource->geonames_code,
            'fips_code'     => $this->resource->fips_code,
            'timezone'      => $this->resource->timezone,
            'country_iso'   => $this->resource->country_iso,
            'state_iso'     => $this->resource->state_iso,
            'state_code'    => $this->resource->state_code,
            'post_codes'    => $this->resource->post_codes,
            'ordering'      => $this->resource->ordering,
            'url'           => sprintf(
                '/localize/country/%d/state/%d/city/browse',
                $this->resource->country->entityId(),
                $this->resource->entityId()
            ),
        ];
    }
}
