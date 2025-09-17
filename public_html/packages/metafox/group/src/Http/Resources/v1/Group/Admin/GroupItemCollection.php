<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class GroupItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class GroupItemCollection extends ResourceCollection
{
    public $collects = GroupItem::class;
}
