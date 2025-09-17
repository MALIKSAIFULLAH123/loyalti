<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use MetaFox\Sms\Rules\PhoneNumberRule;

/**
 * Class EmailOrPhoneNumberRule.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EmailOrPhoneNumberRule implements Rule
{
    public function passes($attribute, $value)
    {
        $isEmail = Validator::make(
            [$attribute => $value],
            [$attribute => 'email']
        )->passes();

        $isPhoneNumber = Validator::make(
            [$attribute => $value],
            [$attribute => new PhoneNumberRule()]
        )->passes();

        return $isEmail || $isPhoneNumber;
    }

    public function message()
    {
        return __p('validation.invalid_email_or_phone');
    }
}
