<?php

namespace MetaFox\Advertise\Traits\Advertise;

trait StatisticTrait
{
    public function getStatistics(): array
    {
        return [
            'total_clicks'        => $this->resource->total_click,
            'total_impressions'   => $this->resource->total_impression,
            'current_clicks'      => $this->resource->statistic->total_click,
            'current_impressions' => $this->resource->statistic->total_impression,
        ];
    }
}
