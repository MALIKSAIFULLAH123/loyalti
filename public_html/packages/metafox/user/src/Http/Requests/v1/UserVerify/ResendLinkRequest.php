<?php

namespace MetaFox\User\Http\Requests\v1\UserVerify;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Sms\Rules\PhoneNumberRule;

/**
 * @deprecated Need remove for some next version
 */
class ResendLinkRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'        => ['required_without:phone_number', 'prohibits:phone_number', 'string', 'email', 'exists:users,email'],
            'phone_number' => ['required_without:email', 'prohibits:email', 'string', new PhoneNumberRule(), 'exists:users,phone_number'],
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
