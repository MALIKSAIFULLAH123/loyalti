<?php

namespace MetaFox\Story\Http\Resources\v1\Story;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class StoryItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class StoryItemCollection extends ResourceCollection
{
    public $collects = StoryItem::class;
}
