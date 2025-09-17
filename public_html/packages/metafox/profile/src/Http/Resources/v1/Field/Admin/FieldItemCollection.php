<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * class FieldItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class FieldItemCollection extends ResourceCollection
{
    public $collects = FieldItem::class;
}
