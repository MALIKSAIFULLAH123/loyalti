<?php

namespace MetaFox\InAppPurchase\Http\Resources\v1\Product;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class ProductItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ProductItemCollection extends ResourceCollection
{
    public $collects = ProductItem::class;
}
