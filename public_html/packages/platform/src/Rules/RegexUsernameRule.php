<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Platform\Facades\Settings;
use MetaFox\RegexRule\Support\Facades\Regex;

class RegexUsernameRule implements Rule
{
    public function __construct()
    {
    }

    public function passes($attribute, $value)
    {
        $regexSetting = Regex::getUsernameRegexSetting();

        return (bool)preg_match("/$regexSetting/", $value);
    }

    public function message()
    {
        return __p(Settings::get('regex.user_name_regex_error_message'));
    }
}
