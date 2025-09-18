<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class BackgroundSetItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class BackgroundSetItemCollection extends ResourceCollection
{
    public $collects = BackgroundSetItem::class;
}
