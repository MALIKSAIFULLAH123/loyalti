<?php

namespace MetaFox\Layout\Http\Resources\v1\Variant\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * class VariantItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class VariantItemCollection extends ResourceCollection
{
    public $collects = VariantItem::class;
}
