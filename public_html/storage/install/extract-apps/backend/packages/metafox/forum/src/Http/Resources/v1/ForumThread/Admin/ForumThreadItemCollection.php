<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class ForumThreadItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ForumThreadItemCollection extends ResourceCollection
{
    public $collects = ForumThreadItem::class;
}
