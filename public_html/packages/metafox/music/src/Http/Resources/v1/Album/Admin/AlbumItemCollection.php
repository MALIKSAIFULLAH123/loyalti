<?php

namespace MetaFox\Music\Http\Resources\v1\Album\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class AlbumItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class AlbumItemCollection extends ResourceCollection
{
    public $collects = AlbumItem::class;
}
