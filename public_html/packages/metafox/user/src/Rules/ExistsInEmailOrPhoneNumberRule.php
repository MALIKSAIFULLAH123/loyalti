<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use MetaFox\Sms\Rules\PhoneNumberRule;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class ExistsInEmailOrPhoneNumberRule.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ExistsInEmailOrPhoneNumberRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = resolve(UserRepositoryInterface::class)->findUserByEmailOrPhoneNumber($value);

        if (null == $user) {
            return false;
        }

        $isEmail = Validator::make(
            [$attribute => $value],
            [$attribute => 'email']
        )->passes();

        if ($isEmail && !$user->hasVerifiedEmail()) {
            abort(401, json_encode([
                'message' => __p('user::validation.pending_email_verification'),
                'title'   => __p('core::phrase.alert'),
            ]));
        }

        $isPhoneNumber = Validator::make(
            [$attribute => $value],
            [$attribute => new PhoneNumberRule()]
        )->passes();

        if ($isPhoneNumber && !$user->hasVerifiedPhoneNumber()) {
            abort(401, json_encode([
                'message' => __p('user::validation.pending_phone_number_verification'),
                'title'   => __p('core::phrase.alert'),
            ]));
        }

        return true;
    }

    public function message()
    {
        return __p('user::validation.cannot_find_this_user');
    }
}
