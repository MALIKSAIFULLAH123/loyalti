<?php

namespace MetaFox\Broadcast\Http\Resources\v1\Connection\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class ConnectionItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ConnectionItemCollection extends ResourceCollection
{
    public $collects = ConnectionItem::class;
}
