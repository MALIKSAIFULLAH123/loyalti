<?php

namespace MetaFox\EMoney\Http\Resources\v1\Transaction;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class TransactionItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class TransactionItemCollection extends ResourceCollection
{
    public $collects = TransactionItem::class;
}
