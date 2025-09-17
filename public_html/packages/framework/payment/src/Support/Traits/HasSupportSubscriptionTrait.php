<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Support\Traits;

use Illuminate\Support\Arr;
use RuntimeException;

/**
 * Trait HasSupportSubscriptionTrait.
 */
trait HasSupportSubscriptionTrait
{
    /**
     * getSupportedBillingFrequency
     * return the supported gateway billing frequency which was previously mapped.
     *
     * @param  string $frequency
     * @return string
     */
    protected function getSupportedBillingFrequency(string $frequency): string
    {
        if (!Arr::has($this->billingFrequency, $frequency)) {
            throw new RuntimeException('Unsupported billing frequency');
        }

        return Arr::get($this->billingFrequency, $frequency);
    }
}
