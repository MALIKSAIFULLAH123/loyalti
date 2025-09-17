<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionStatistic;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class StatisticItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class StatisticItemCollection extends ResourceCollection
{
    public $collects = StatisticItem::class;
}
