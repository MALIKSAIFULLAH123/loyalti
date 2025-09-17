<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Invoice;

use Foxexpert\Sevent\Models\Invoice as Model;
use Foxexpert\Sevent\Support\Browse\Scopes\Invoice\ViewScope;

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
 */
class SearchSoldInvoiceForm extends SearchBoughtInvoiceForm
{
    protected string $view =  ViewScope::VIEW_SOLD;
}
