<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class WithdrawRequestItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class WithdrawRequestItemCollection extends ResourceCollection
{
    public $collects = WithdrawRequestItem::class;
}
