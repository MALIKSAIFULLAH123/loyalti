<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryCity\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Localize\Models\CountryCity as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class CountryCityItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class CountryCityItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->resource->id,
            'state_code' => $this->resource->state_code,
            'city_code'  => $this->resource->city_code,
            'fips_code'  => $this->resource->fips_code,
            'post_codes' => $this->resource->post_codes,
            'name'       => $this->resource->name,
            'longitude'  => $this->resource->longitude,
            'latitude'   => $this->resource->latitude,
            'capital'    => $this->resource->capital,
            'population' => $this->resource->population,
            'ordering'   => $this->resource->ordering,
        ];
    }
}
