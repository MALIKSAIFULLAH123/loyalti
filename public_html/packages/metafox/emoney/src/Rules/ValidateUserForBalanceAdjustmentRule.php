<?php
namespace MetaFox\EMoney\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class ValidateUserForBalanceAdjustmentRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = resolve(UserRepositoryInterface::class)->find($value);

        if ($user instanceof User && $user->isApproved() && $user->hasVerified()) {
            return;
        }

        $fail(__p('ewallet::validation.invalid_user'));
    }
}
