<?php

namespace MetaFox\Platform\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Contracts\HasLocationCheckin;

/**
 * @property HasLocationCheckin $resource
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class LocationResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'address' => $this->resource->location_name,
            'lat'     => $this->resource->location_latitude,
            'lng'     => $this->resource->location_longitude,
        ];
    }
}
