<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class BgsCollectionItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class BgsCollectionItemCollection extends ResourceCollection
{
    public $collects = BgsCollectionItem::class;
}
