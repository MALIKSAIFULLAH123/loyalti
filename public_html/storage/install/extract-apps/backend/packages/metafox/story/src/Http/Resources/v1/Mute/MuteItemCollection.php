<?php

namespace MetaFox\Story\Http\Resources\v1\Mute;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class MuteItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class MuteItemCollection extends ResourceCollection
{
    public $collects = MuteItem::class;
}
