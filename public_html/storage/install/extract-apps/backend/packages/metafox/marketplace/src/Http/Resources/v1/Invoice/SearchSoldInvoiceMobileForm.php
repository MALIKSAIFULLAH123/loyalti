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
 * Class SearchSoldInvoiceMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName marketplace_invoice.sold_search
 * @driverType form
 */
class SearchSoldInvoiceMobileForm extends SearchBoughtInvoiceMobileForm
{
    protected string $view =  ViewScope::VIEW_SOLD;
}
