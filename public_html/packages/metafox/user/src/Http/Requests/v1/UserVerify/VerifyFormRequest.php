<?php

namespace MetaFox\User\Http\Requests\v1\UserVerify;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Sms\Rules\PhoneNumberRule;
use MetaFox\User\Support\Facades\UserVerify;
use MetaFox\User\Support\UserVerifySupport;

class VerifyFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action'       => ['required', 'string', new AllowInRule(UserVerify::getAllowedActions(UserVerifySupport::WEB_SERVICE))],
            'user_id'      => ['required', 'numeric', 'exists:users,id'],
            'email'        => ['required_without:phone_number', 'prohibits:phone_number', 'string', 'email', 'exists:users,email'],
            'phone_number' => ['required_without:email', 'prohibits:email', 'string', new PhoneNumberRule(), 'exists:users,phone_number'],
            'resolution'   => ['string', 'sometimes', 'nullable', new AllowInRule(['web', 'mobile'])],
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
