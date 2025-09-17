<?php
namespace MetaFox\Featured\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class ToDateRule implements ValidationRule, DataAwareRule
{
    /**
     * @var array
     */
    protected mixed $data = null;

    /**
     * @param $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (null === $value) {
            return;
        }

        if (!is_array($this->data)) {
            return;
        }

        $fromValue = Arr::get($this->data, 'from_date');

        if (!is_string($fromValue)) {
            return;
        }

        $fromValue = trim($fromValue);

        if (\MetaFox\Platform\MetaFoxConstant::EMPTY_STRING === $fromValue) {
            return;
        }

        $fromDate = Carbon::parse($fromValue);

        $toDate = Carbon::parse($value);

        if ($toDate->greaterThanOrEqualTo($fromDate)) {
            return;
        }

        $fail(__p('featured::validation.to_date_should_be_greater_than_the_to_date'));
    }
}
