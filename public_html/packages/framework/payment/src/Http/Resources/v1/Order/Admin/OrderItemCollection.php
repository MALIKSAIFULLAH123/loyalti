<?php

namespace MetaFox\Payment\Http\Resources\v1\Order\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class OrderItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class OrderItemCollection extends ResourceCollection
{
    public $collects = OrderItem::class;
}
