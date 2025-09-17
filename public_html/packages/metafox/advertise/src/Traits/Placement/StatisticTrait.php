<?php

namespace MetaFox\Advertise\Traits\Placement;

trait StatisticTrait
{
    public function getStatistics(): array
    {
        return [
            'total_advertises' => $this->resource->advertises_count,
        ];
    }
}
