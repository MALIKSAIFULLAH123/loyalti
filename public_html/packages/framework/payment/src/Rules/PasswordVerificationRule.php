<?php
namespace MetaFox\Payment\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordVerificationRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail(__p('payment::validation.verification_password_is_required'));
            return;
        }

        $context = user();

        if ($context->isGuest()) {
            $fail(__p('core::validation.this_action_is_unauthorized'));
            return;
        }

        if ($context->validatePassword($value)) {
            return;
        }

        $fail(__p('auth.password'));
    }
}
