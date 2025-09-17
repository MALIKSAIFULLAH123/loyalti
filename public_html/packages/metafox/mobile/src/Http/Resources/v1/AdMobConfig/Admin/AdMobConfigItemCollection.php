<?php

namespace MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class AdMobConfigItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class AdMobConfigItemCollection extends ResourceCollection
{
    public $collects = AdMobConfigItem::class;
}
