<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use MetaFox\Platform\Facades\Settings;

/**
 * Class MetaFoxPasswordFormatRule.
 */
class MetaFoxPasswordFormatRule implements Rule
{
    /**
     * Uppercase character pattern for a strong password.
     */
    public const UPPERCASE_CHARACTER_PATTERN = '/[A-Z]/';

    /**
     * Lower character pattern for a strong password.
     */
    public const LOWERCASE_CHARACTER_PATTERN = '/[a-z]/';

    /**
     * Number pattern for a strong password.
     */
    public const NUMBER_PATTERN = '/[0-9]/';

    /**
     * Special character pattern for a strong password.
     */
    public const SPECIAL_CHARACTER_PATTERN = '/[!"#$%&\'()*+,\-.\/:;<=>?@[\]^_`{|}~]/';

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        $validator = Validator::make(['password' => $value], [
            'password' => $this->getRequestRules(),
        ]);

        return $validator->passes();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        $minLength = Settings::get('user.minimum_length_for_password');

        $maxLength = Settings::get('user.maximum_length_for_password');

        $var = 'validation.password_field_validation_description';

        if ($this->isStrongPassword()) {
            $var = 'validation.password_field_validation_strong';
        }

        return __p($var, [
            'minLength' => $minLength,
            'maxLength' => $maxLength,
        ]);
    }

    /**
     * @return bool
     */
    public function isStrongPassword(): bool
    {
        return Settings::get('user.required_strong_password', false);
    }

    public function getRequestRules(): array
    {
        $rules = array_map(function ($rule) {
            return 'regex:/' . $rule . '/';
        }, $this->getFormRules());

        $rules[] = 'regex:' . $this->getMaxLengthRule();

        return $rules;
    }

    public function getFormRules(): array
    {
        $passwordParams = [
            $this->getMinLengthRule(),
        ];

        if ($this->isStrongPassword()) {
            $passwordParams = array_merge($passwordParams, $this->getStrongPasswordFormRule());
        }

        return $passwordParams;
    }

    public function getStrongPasswordFormRule(): array
    {
        return [
            $this->getUpperCaseRule(),
            $this->getLowerCaseRule(),
            $this->getNumberRule(),
            $this->getSpecialCharactersRule(),
        ];
    }

    protected function getMinLengthRule(): string
    {
        $minLength = Settings::get('user.minimum_length_for_password');

        if ($minLength == 1) {
            return '\S{1,}';
        }

        if ($minLength == 2) {
            return '\S{2,}';
        }

        return '\S{1,}.{' . $minLength - 2 . ',}\S{1,}';
    }

    protected function getMaxLengthRule(): string
    {
        $maxLength = Settings::get('user.maximum_length_for_password');

        if ($maxLength == 1) {
            return '/\S{1,}/';
        }

        if ($maxLength == 2) {
            return '/\S{2,}/';
        }

        return '/^\S{1}.{1,' . ($maxLength - 2) . '}\S{1}$/';
    }

    protected function getUpperCaseRule(): string
    {
        return trim(self::UPPERCASE_CHARACTER_PATTERN, '/');
    }

    protected function getLowerCaseRule(): string
    {
        return trim(self::LOWERCASE_CHARACTER_PATTERN, '/');
    }

    protected function getNumberRule(): string
    {
        return trim(self::NUMBER_PATTERN, '/');
    }

    protected function getSpecialCharactersRule(): string
    {
        return trim(self::SPECIAL_CHARACTER_PATTERN, '/');
    }
}
