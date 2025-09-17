<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class PlaylistItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class PlaylistItemCollection extends ResourceCollection
{
    public $collects = PlaylistItem::class;
}
