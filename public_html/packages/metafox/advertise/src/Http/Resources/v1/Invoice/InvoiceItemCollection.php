<?php

namespace MetaFox\Advertise\Http\Resources\v1\Invoice;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class InvoiceItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class InvoiceItemCollection extends ResourceCollection
{
    public $collects = InvoiceItem::class;
}
