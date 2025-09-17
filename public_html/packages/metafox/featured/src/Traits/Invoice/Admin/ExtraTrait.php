<?php
namespace MetaFox\Featured\Traits\Invoice\Admin;

use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Policies\InvoicePolicy;

/**
 * @property Invoice $resource
 */
trait ExtraTrait
{
    public function getExtra(): array
    {
        $context = user();

        /**
         * @var InvoicePolicy $policy
         */
        $policy = resolve(InvoicePolicy::class);

        return [
            'can_cancel'       => $policy->cancel($context, $this->resource),
            'can_mark_as_paid' => $policy->markAsPaid($context, $this->resource),
        ];
    }
}
