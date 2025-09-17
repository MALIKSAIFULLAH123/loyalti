<?php

namespace MetaFox\ActivityPoint\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MetaFox\ActivityPoint\Support\Facade\PointConversion as Facade;

class ValidateConversionRequestAmountRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currency   = app('currency')->getDefaultCurrencyId();
        $total      = Facade::getConversionAmount($value, $currency);
        $commission = Facade::getCommissionFee($total);
        $actual     = round($total - $commission, 2);

        if ($actual < 0.01) {
            $fail(__p('activitypoint::validation.the_amount_you_receive_must_be_greater_than_or_equal_to_amount', [
                'amount' => app('currency')->getPriceFormatByCurrencyId($currency, 0.01),
            ]));
        }
    }
}
