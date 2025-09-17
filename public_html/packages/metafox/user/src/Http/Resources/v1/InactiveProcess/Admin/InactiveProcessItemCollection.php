<?php

namespace MetaFox\User\Http\Resources\v1\InactiveProcess\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class InactiveProcessItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class InactiveProcessItemCollection extends ResourceCollection
{
    public $collects = InactiveProcessItem::class;
}
