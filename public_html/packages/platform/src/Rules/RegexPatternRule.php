<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;

class RegexPatternRule implements Rule
{
    public function passes($attribute, $value)
    {
        try {
            preg_match("/$value/", $value);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function message()
    {
        return __p('core::phrase.your_regex_pattern_contains_one_or_more_errors');
    }
}
