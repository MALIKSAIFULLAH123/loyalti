<?php

namespace MetaFox\EMoney\Http\Resources\v1\CurrencyConverter\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class CurrencyConverterItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class CurrencyConverterItemCollection extends ResourceCollection
{
    public $collects = CurrencyConverterItem::class;
}
