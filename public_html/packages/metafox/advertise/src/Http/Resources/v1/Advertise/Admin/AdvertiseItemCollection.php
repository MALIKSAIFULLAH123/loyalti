<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class AdvertiseItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class AdvertiseItemCollection extends ResourceCollection
{
    public $collects = AdvertiseItem::class;
}
