<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use MetaFox\Platform\Contracts\User;
use Closure;

/**
 * Class ValidatePasswordRule.
 */
class ValidatePasswordRule implements ValidationRule
{
    public function __construct(protected User $context)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->context->validatePassword($value)) {
            return;
        }

        $fail(__p('user::phrase.password_is_not_correct'));
    }
}
