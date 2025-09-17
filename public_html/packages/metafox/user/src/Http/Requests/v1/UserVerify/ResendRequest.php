<?php

namespace MetaFox\User\Http\Requests\v1\UserVerify;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Sms\Rules\PhoneNumberRule;
use MetaFox\User\Rules\ExistsInMultipleTablesRule;
use MetaFox\User\Support\Facades\UserVerify;
use MetaFox\User\Support\UserVerifySupport;

class ResendRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $existsEmailRule       = new ExistsInMultipleTablesRule('exists:users,email|exists:user_verify,verifiable');
        $existsPhoneNumberRule = new ExistsInMultipleTablesRule('exists:users,phone_number|exists:user_verify,verifiable');

        return [
            'action'       => ['required', 'string', new AllowInRule(UserVerify::getAllowedActions(UserVerifySupport::WEB_SERVICE))],
            'user_id'      => ['required', 'numeric', 'exists:users,id'],
            'email'        => ['required_without:phone_number', 'prohibits:phone_number', 'string', 'email', $existsEmailRule],
            'phone_number' => ['required_without:email', 'prohibits:email', 'string', new PhoneNumberRule(), $existsPhoneNumberRule],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'email.exists'        => __p('user::validation.account_verified'),
            'phone_number.exists' => __p('user::validation.account_verified'),
        ];
    }
}
