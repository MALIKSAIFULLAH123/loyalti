<?php

namespace MetaFox\Featured\Http\Resources\v1\Package;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class PackageItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageItemCollection extends ResourceCollection
{
    public $collects = PackageItem::class;
}
