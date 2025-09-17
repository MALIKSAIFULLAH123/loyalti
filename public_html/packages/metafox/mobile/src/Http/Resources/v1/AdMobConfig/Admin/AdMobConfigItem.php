<?php

namespace MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Mobile\Models\AdMobConfig as Model;
use MetaFox\Mobile\Models\AdMobConfig;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class AdMobConfigItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class AdMobConfigItem extends JsonResource
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
            'id'                      => $this->resource->entityId(),
            'resource_name'           => $this->resource->entityType(),
            'name'                    => $this->resource->name,
            'frequency_capping'       => $this->resource->frequency_capping,
            'frequency_capping_title' => $this->resource->frequency_capping_title,
            'view_capping'            => $this->resource->view_capping,
            'time_capping_impression' => $this->resource->time_capping_impression,
            'time_capping_frequency'  => $this->convertTime($this->resource->time_capping_frequency),
            'location_priority'       => $this->resource->location_priority,
            'type'                    => $this->resource->type,
            'type_name'               => $this->resource->type_name,
            'is_active'               => $this->resource->is_active,
            'is_sticky'               => $this->resource->is_active,
        ];
    }

    public function convertTime(?string $frequency): int
    {
        $minute = 60;
        $hour   = 60 * $minute;
        $day    = 24 * $hour;

        return match ($frequency) {
            AdMobConfig::AD_MOB_FREQUENCY_PER_MINUTE => $minute,
            AdMobConfig::AD_MOB_FREQUENCY_PER_HOUR   => $hour,
            AdMobConfig::AD_MOB_FREQUENCY_PER_DAY    => $day,
            default                                  => 0
        };
    }
}
