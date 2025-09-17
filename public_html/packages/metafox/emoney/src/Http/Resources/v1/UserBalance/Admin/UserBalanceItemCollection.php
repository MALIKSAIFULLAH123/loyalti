<?php

namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class UserBalanceItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class UserBalanceItemCollection extends ResourceCollection
{
    public $collects = UserBalanceItem::class;
}
