<?php

namespace MetaFox\RegexRule\Support;

use Illuminate\Support\Str;
use MetaFox\RegexRule\Contracts\Regex as ContractRegex;
use MetaFox\Platform\Facades\Settings;

class Regex implements ContractRegex
{
    public function getRegexSetting(string $fieldName): mixed
    {
        $settingName = $fieldName . '_regex_rule';

        return Settings::get('regex.' . $settingName);
    }

    public function getUsernameRegexSetting(): mixed
    {
        $usernameRegex = Settings::get('regex.user_name_regex_rule');

        return Str::replace(['x7f', 'xff'], ['x21', 'x7e'], $usernameRegex);
    }

    public function getRegexErrorMessage(string $fieldName, bool $toVarName = true): mixed
    {
        $settingName = $fieldName . '_regex_error_message';
        $errorName   = Settings::get('regex.' . $settingName, 'validation.regex');

        if ($toVarName) {
            return $errorName;
        }

        return __p($errorName);
    }
}
