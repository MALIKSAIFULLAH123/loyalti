<?php

namespace MetaFox\Group\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;

class MuteTimeRule implements RuleContract
{
    public const GENERAL_REGEX = '/^((0[.]([1-9]){1,2})[dh]|([1-9]+([0]+)?([.]([1-9]){1,2})?)[dh]|[1-9]+([0]+)?[m])(\s((0[.]([1-9]){1,2})[dh]|([1-9]+([0]+)?([.]([1-9]){1,2})?)[dh]|[1-9]+([0]+)?[m]))?$/u';

    public const COMBINATION_REGEX = '/^([\d.]+[d](\s[\d.]+[hm])?|[\d.]+[h](\s[\d.]+[m])?|[\d.]+[m])$/';

    protected int $errorType = 1;

    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return false;
        }

        if (!preg_match(self::GENERAL_REGEX, $value)) {
            return false;
        }

        if (!preg_match(self::COMBINATION_REGEX, $value)) {
            $this->errorType = 2;

            return false;
        }

        return true;
    }

    public function message(): string
    {
        if ($this->errorType == 2) {
            return __p('group::phrase.time_muted_member_option_regex_combination_error');
        }

        return __p('group::phrase.time_muted_member_option_regex_error');
    }
}
