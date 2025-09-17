<?php

namespace MetaFox\Story\Http\Resources\v1\StoryReaction;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class StoryReactionItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class StoryReactionItemCollection extends ResourceCollection
{
    public $collects = StoryReactionItem::class;
}
