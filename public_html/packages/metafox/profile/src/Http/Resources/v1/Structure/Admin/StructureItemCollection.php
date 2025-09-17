<?php

namespace MetaFox\Profile\Http\Resources\v1\Structure\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * class StructureItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class StructureItemCollection extends ResourceCollection
{
    public $collects = StructureItem::class;
}
