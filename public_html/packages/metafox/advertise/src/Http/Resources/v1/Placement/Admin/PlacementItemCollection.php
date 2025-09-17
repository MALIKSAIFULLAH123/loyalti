<?php

namespace MetaFox\Advertise\Http\Resources\v1\Placement\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class PlacementItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class PlacementItemCollection extends ResourceCollection
{
    public $collects = PlacementItem::class;
}
