<?php

namespace MetaFox\Core\Http\Resources\v1\Statistic;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class StatisticItemCollection extends ResourceCollection
{
    public $collects = StatisticItem::class;
}
