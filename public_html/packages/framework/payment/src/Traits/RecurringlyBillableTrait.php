<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Traits;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Payment\Contracts\IsRecurringlyBillable;
use MetaFox\Payment\Support\Payment;

/**
 * Trait RecurringlyBillableTrait.
 *
 * @mixin Model
 * @mixin IsRecurringlyBillable
 */
trait RecurringlyBillableTrait
{
    use BillableTrait {
        toOrder as toBillableOrder;
    }

    public function toOrder(): array
    {
        return array_merge($this->toBillableOrder(), [
            'trial_frequency'   => $this->getTrialFrequency(),
            'trial_interval'    => $this->getTrialInterval(),
            'trial_amount'      => $this->getTrialAmount(),
            'billing_frequency' => $this->getBillingFrequency(),
            'billing_interval'  => $this->getBillingInterval(),
            'billing_amount'    => $this->getBillingAmount(),
        ]);
    }

    public function getTrialFrequency(): ?string
    {
        return $this->trial_frequency;
    }

    public function getTrialInterval(): int
    {
        return $this->trial_interval;
    }

    public function getTrialAmount(): float
    {
        return $this->trial_amount;
    }

    public function getBillingFrequency(): ?string
    {
        return $this->billing_frequency;
    }

    public function getBillingInterval(): int
    {
        return $this->billing_interval;
    }

    public function getBillingAmount(): float
    {
        return $this->billing_amount;
    }

    public function getPaymentType(): string
    {
        return Payment::PAYMENT_RECURRING;
    }
}
