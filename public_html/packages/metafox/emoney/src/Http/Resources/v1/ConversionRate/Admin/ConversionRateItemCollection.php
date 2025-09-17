<?php

namespace MetaFox\EMoney\Http\Resources\v1\ConversionRate\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class ConversionRateItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ConversionRateItemCollection extends ResourceCollection
{
    public $collects = ConversionRateItem::class;
}
