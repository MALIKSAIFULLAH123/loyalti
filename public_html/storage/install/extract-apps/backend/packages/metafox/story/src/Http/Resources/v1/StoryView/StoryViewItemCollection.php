<?php

namespace MetaFox\Story\Http\Resources\v1\StoryView;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class StoryViewItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class StoryViewItemCollection extends ResourceCollection
{
    public $collects = StoryViewItem::class;
}
