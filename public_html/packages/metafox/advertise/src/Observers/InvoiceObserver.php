<?php

namespace MetaFox\Advertise\Observers;

use MetaFox\Advertise\Models\Invoice;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class InvoiceObserver.
 */
class InvoiceObserver
{
    public function deleted(Invoice $invoice): void
    {
        $invoice->transactions()->delete();
    }
}

// end stub
