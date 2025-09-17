<?php

namespace MetaFox\Music\Support\Browse\Traits\Genre;

use MetaFox\Music\Models\Genre;

/**
 * @property Genre $resource
 */
trait StatisticTrait
{
    public function getStatistic(): array
    {
        return [
            'total_song'  => $this->resource->total_track,
            'total_album' => $this->resource->total_album,
            'total_item'  => $this->resource->total_item,
            'total_sub'   => $this->resource->subCategories->count(),
        ];
    }
}
