<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/**
 * Class ExistsInMultipleTablesRule.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ExistsInMultipleTablesRule implements Rule
{
    protected string $rules;

    public function __construct(string $rules)
    {
        $this->rules = $rules;
    }

    public function passes($attribute, $value): bool
    {
        $rules = explode('|', $this->rules);

        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $rule) {
            $isExist = Validator::make(
                [$attribute => $value],
                [$attribute => [$rule]]
            )->passes();

            if ($isExist) {
                return true;
            }
        }

        return false;
    }

    public function message(): string
    {
        return __p('user::validation.value_does_not_exist_in_any_of_the_specified_tables');
    }
}
