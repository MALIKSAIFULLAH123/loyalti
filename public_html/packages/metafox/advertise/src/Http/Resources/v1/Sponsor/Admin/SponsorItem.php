<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Sponsor as Model;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Traits\Sponsor\ExtraTrait;
use MetaFox\Advertise\Traits\Sponsor\StatisticTrait;
use MetaFox\Platform\Facades\ResourceGate;

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
class SponsorItem extends JsonResource
{
    use StatisticTrait;
    use ExtraTrait;

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
            'user'          => ResourceGate::user($this->resource->userEntity),
            'status'        => $this->resource->status_text,
            'is_active'     => $this->resource->is_active,
            'start_date'    => $this->toDate($this->resource->start_date),
            'end_date'      => $this->toDate($this->resource->end_date),
            'age_from'      => $this->resource->age_from,
            'age_to'        => $this->resource->age_to,
            'statistic'     => $this->getStatistics(),
            'is_pending'    => $this->resource->is_pending,
            'is_approved'   => $this->resource->is_approved,
            'is_denied'     => $this->resource->is_denied,
            'sponsor_type'  => $this->resource->sponsor_type_text,
            'extra'         => $this->getExtra(),
            'created_at'    => $this->toDate($this->resource->created_at),
            'updated_at'    => $this->toDate($this->resource->updated_at),
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
        ];
    }

    protected function toDate(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->toISOString();
    }
}
