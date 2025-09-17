<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class ForumPostItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ForumPostItemCollection extends ResourceCollection
{
    public $collects = ForumPostItem::class;
}
