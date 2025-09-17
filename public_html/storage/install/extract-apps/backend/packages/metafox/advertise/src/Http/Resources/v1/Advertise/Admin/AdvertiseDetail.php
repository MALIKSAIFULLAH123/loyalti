<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Advertise\Traits\Advertise\ExtraTrait;
use MetaFox\Advertise\Traits\Advertise\StatisticTrait;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class AdvertiseDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class AdvertiseDetail extends JsonResource
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
            'id'              => $this->resource->entityId(),
            'module_name'     => 'advertise',
            'resource_name'   => $this->resource->entityType(),
            'title'           => $this->resource->toTitle(),
            'placement'       => $this->getPlacement(),
            'user'            => ResourceGate::user($this->resource->userEntity),
            'creation_type'   => $this->resource->creation_type,
            'status'          => $this->resource->status_text,
            'is_active'       => $this->resource->is_active,
            'advertise_url'   => $this->resource->url,
            'start_date'      => $this->toDate($this->resource->start_date),
            'end_date'        => $this->toDate($this->resource->end_date),
            'advertise_type'  => $this->resource->advertise_type,
            'age_from'        => $this->resource->age_from,
            'age_to'          => $this->resource->age_to,
            'advertise_image' => $this->resource->images,
            'image_values'    => $this->resource->image_values,
            'html_values'     => $this->resource->html_values,
            'statistic'       => $this->getStatistics(),
            'is_pending'      => $this->resource->is_pending,
            'is_approved'     => $this->resource->is_approved,
            'is_completed'    => $this->resource->is_completed,
            'is_denied'       => $this->resource->is_denied,
            'extra'           => $this->getExtra(),
            'created_at'      => $this->toDate($this->resource->created_at),
            'updated_at'      => $this->toDate($this->resource->updated_at),
            'link'            => $this->resource->toLink(),
        ];
    }

    protected function toDate(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->toISOString();
    }

    protected function getPlacement(): ?JsonResource
    {
        if (null === $this->resource->placement) {
            return null;
        }

        return ResourceGate::asEmbed($this->resource->placement, null);
    }
}
