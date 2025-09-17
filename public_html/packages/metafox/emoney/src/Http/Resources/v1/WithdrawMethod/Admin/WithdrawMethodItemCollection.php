<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawMethod\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class WithdrawMethodItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class WithdrawMethodItemCollection extends ResourceCollection
{
    public $collects = WithdrawMethodItem::class;
}
