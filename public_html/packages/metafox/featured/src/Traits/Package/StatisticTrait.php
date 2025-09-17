<?php
namespace MetaFox\Featured\Traits\Package;

use MetaFox\Featured\Models\Package;

/**
 * @property Package $resource
 */
trait StatisticTrait
{
    public function getStatistic(): array
    {
        return [
            'total_active' => $this->resource->total_active,
            'total_end' => $this->resource->total_end,
            'total_cancelled' => $this->resource->total_cancelled,
        ];
    }
}
