<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class LiveVideoItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class LiveVideoItemCollection extends ResourceCollection
{
    public $collects = LiveVideoItem::class;
}
