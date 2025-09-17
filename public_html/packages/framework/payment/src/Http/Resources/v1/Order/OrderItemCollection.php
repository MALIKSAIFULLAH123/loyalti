<?php

namespace MetaFox\Payment\Http\Resources\v1\Order;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * |--------------------------------------------------------------------------
 * | Resource Pattern
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/item_collection.stub.
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
