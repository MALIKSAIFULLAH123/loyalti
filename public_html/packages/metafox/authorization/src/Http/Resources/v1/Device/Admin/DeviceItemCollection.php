<?php

namespace MetaFox\Authorization\Http\Resources\v1\Device\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class DeviceItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class DeviceItemCollection extends ResourceCollection
{
    public $collects = DeviceItem::class;
}
