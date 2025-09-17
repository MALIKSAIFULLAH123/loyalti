<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\User\Support\Facades\UserBirthday;

/**
 * Class MonthDayBirthdayFormatRule.
 */
class MonthDayBirthdayFormatRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     * @param string $attribute
     * @param string $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        return in_array($value, UserBirthday::getBirthdayFormatsWithoutYear());
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('validation.in_array', ['other' => implode(', ', UserBirthday::getBirthdayFormatsWithoutYear())]);
    }
}
