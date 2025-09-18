<?php

namespace MetaFox\Music\Http\Resources\v1\Song\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class SongItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class SongItemCollection extends ResourceCollection
{
    public $collects = SongItem::class;
}
