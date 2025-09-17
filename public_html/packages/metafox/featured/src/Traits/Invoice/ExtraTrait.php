<?php
namespace MetaFox\Featured\Traits\Invoice;

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
            'can_payment' => $policy->prepayment($context, $this->resource),
            'can_cancel'  => $policy->cancel($context, $this->resource),
        ];
    }
}
