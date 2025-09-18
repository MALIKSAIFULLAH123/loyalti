<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Marketplace\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchSoldInvoiceForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName marketplace_invoice.sold_search
 * @driverType form
 * @preload    1
 */
class SearchSoldInvoiceForm extends SearchBoughtInvoiceForm
{
    protected string $view =  ViewScope::VIEW_SOLD;
}
