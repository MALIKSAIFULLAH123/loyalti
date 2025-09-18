<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class PageItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class PageItemCollection extends ResourceCollection
{
    public $collects = PageItem::class;
}
