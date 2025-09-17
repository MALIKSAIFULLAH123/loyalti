<?php

namespace MetaFox\Featured\Http\Resources\v1\Item;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class ItemItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ItemItemCollection extends ResourceCollection
{
    public $collects = ItemItem::class;
}
