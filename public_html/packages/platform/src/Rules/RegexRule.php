<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\RegexRule\Support\Facades\Regex;

class RegexRule implements Rule
{
    protected $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function passes($attribute, $value)
    {
        $regexSetting = Regex::getRegexSetting($this->field);

        return (bool) preg_match("/$regexSetting/", $value);
    }

    public function message()
    {
        return __p(Regex::getRegexErrorMessage($this->field));
    }
}
