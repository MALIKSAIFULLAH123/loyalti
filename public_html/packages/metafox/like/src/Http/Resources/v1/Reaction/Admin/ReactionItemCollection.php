<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class ReactionItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ReactionItemCollection extends ResourceCollection
{
    public $collects = ReactionItem::class;
}
