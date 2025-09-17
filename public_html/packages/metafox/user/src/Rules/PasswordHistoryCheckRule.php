<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Repositories\UserPasswordHistoryRepositoryInterface;

class PasswordHistoryCheckRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        if ($this->forcePasswordHistoryCheck()) {
            $passwords = $this->getHistoryPasswords();
            foreach ($passwords as $password) {
                if (Hash::check($value, $password)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message(): string
    {
        return __p('validation.password_field_validation_history', [
            'count' => Settings::get('user.number_of_password_history'),
        ]);
    }

    public function forcePasswordHistoryCheck(): bool
    {
        return Settings::get('user.force_password_history_check', false);
    }

    protected function getHistoryPasswords(): array
    {
        $limit = Settings::get('user.number_of_password_history', 1);

        $passwords = resolve(UserPasswordHistoryRepositoryInterface::class)->getHistoryPasswords(user()->entityId(), $limit);

        return $passwords->pluck('password')->toArray();
    }
}
