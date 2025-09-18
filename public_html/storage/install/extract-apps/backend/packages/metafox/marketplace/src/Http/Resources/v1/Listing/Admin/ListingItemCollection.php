<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class ListingItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ListingItemCollection extends ResourceCollection
{
    public $collects = ListingItem::class;
}
