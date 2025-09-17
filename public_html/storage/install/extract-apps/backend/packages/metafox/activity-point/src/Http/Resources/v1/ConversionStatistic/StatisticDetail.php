<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionStatistic;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\ActivityPoint\Models\ConversionStatistic as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class StatisticDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class StatisticDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request       $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $context = user();
        $pointsInMonth = PointConversion::getExchangedPointsInMonth($context);
        $pointsInYear = PointConversion::getExchangedPointsInYear($context);

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'activitypoint',
            'resource_name' => $this->resource->entityType(),
            'total'         => [
                'total_converted' => $this->resource->total_converted,
                'total_pending'   => $this->resource->total_pending,
                'total_converted_in_month' => $pointsInMonth,
                'total_converted_in_year' => $pointsInYear,
            ],
            'total_formatted' => [
                'total_converted' => __p('activitypoint::web.point_conversion_total_points', [
                    'points' => $this->resource->total_converted,
                    'points_format' => number_format($this->resource->total_converted),
                ]),
                'total_pending'   => __p('activitypoint::web.point_conversion_total_points', [
                    'points' => $this->resource->total_pending,
                    'points_format' => number_format($this->resource->total_pending),
                ]),
                'total_converted_in_month' => __p('activitypoint::web.point_conversion_total_points', [
                    'points' => $pointsInMonth,
                    'points_format' => number_format($pointsInMonth),
                ]),
                'total_converted_in_year' => __p('activitypoint::web.point_conversion_total_points', [
                    'points' => $pointsInYear,
                    'points_format' => number_format($pointsInYear),
                ]),
            ],
        ];
    }
}
