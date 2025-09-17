<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Sponsor as Model;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Traits\Sponsor\ExtraTrait;
use MetaFox\Advertise\Traits\Sponsor\StatisticTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class SponsorItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class SponsorEmbed extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'advertise',
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->toTitle(),
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'router'        => $this->resource->toRouter(),
            'status'        => $this->resource->status_text,
        ];
    }
}
