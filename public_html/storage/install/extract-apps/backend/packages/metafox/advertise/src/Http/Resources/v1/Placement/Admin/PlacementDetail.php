<?php

namespace MetaFox\Advertise\Http\Resources\v1\Placement\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Placement as Model;
use MetaFox\Advertise\Traits\Placement\ExtraTrait;
use MetaFox\Advertise\Traits\Placement\StatisticTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class PlacementDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class PlacementDetail extends JsonResource
{
    use ExtraTrait;
    use StatisticTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $adsLink = null;

        $statistics = $this->getStatistics();

        if (Arr::get($statistics, 'total_advertises') > 0) {
            $adsLink = $this->resource->toAdmincpAdsLink();
        }

        return [
            'id'                  => $this->resource->entityId(),
            'module_name'         => 'advertise',
            'resource_name'       => $this->resource->entityType(),
            'title'               => $this->resource->toTitle(),
            'is_active'           => $this->resource->is_active,
            'price'               => $this->resource->price,
            'allowed_user_roles'  => $this->resource->allowed_user_roles,
            'placement_type'      => $this->resource->placement_type,
            'placement_type_text' => $this->resource->placement_type_text,
            'ads_link'            => $adsLink,
            'extra'               => $this->getExtra(),
            'statistic'           => $statistics,
            'created_at'          => $this->toDate($this->resource->created_at),
            'updated_at'          => $this->toDate($this->resource->updated_at),
        ];
    }

    protected function toDate(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }
}
