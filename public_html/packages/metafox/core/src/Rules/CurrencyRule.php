<?php

namespace MetaFox\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class CurrencyRule implements Rule
{
    /**
     * @var bool
     */
    protected $isPriceError = false;

    public function passes($attribute, $value): bool
    {
        $currencies = app('currency')->getAllActiveCurrencies();

        if (!$this->isValidCurrencyValue($currencies)) {
            return true;
        }

        if (!$this->isValidCurrencyValue($value)) {
            return false;
        }

        foreach ($currencies as $currency) {
            $code = $currency->code;

            if (!Arr::has($value, $code)) {
                return false;
            }

            $price = Arr::get($value, $code);

            $number = $price;

            if (is_array($price)) {
                $number = Arr::get($price, 'value');
            }

            if (!$this->isValidPriceValue($number)) {
                $this->isPriceError = true;

                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return match ($this->isPriceError) {
            true => __p('core::validation.price_must_be_numeric_and_greater_than_or_equal_to_number', ['number' => '0']),
            false => __p('core::validation.you_must_fill_in_all_price_values')
        };
    }

    protected function isValidCurrencyValue(?array $value): bool
    {
        return is_array($value) && count($value);
    }

    protected function isValidPriceValue(?string $value): bool
    {
        return is_numeric($value) && (float) $value >= 0;
    }
}
