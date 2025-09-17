<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class ConversionRequestItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ConversionRequestItemCollection extends ResourceCollection
{
    public $collects = ConversionRequestItem::class;
}
