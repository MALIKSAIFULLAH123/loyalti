<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class PhotoItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class PhotoItemCollection extends ResourceCollection
{
    public $collects = PhotoItem::class;
}
