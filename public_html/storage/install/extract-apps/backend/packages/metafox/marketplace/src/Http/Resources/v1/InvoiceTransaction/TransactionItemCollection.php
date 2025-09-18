<?php

namespace MetaFox\Marketplace\Http\Resources\v1\InvoiceTransaction;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class TransactionItemCollection extends ResourceCollection
{
    public $collects = TransactionItem::class;
}
