<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointSetting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ActivityPoint\Models\PointSetting as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PointSettingItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PointSettingItem extends JsonResource
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
            'id'            => $this->resource->entityId(),
            'resource_name' => $this->resource->entityType(),
            'module_name'   => 'activitypoint',
            'description'   => $this->resource->description,
            'points'        => $this->resource->points,
            'max_earned'    => $this->resource->max_earned,
            'period'        => $this->resource->period,
        ];
    }
}
