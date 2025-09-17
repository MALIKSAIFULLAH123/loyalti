<?php

namespace MetaFox\User\Http\Requests\v1\UserVerify;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\User\Support\Facades\UserVerify;
use MetaFox\User\Support\UserVerifySupport;

class VerifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action'            => ['required', 'string', new AllowInRule(UserVerify::getAllowedActions(UserVerifySupport::WEB_SERVICE))],
            'verification_code' => ['required', 'string', 'regex:/[0-9]{6}$/'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @return array<string>
     */
    /**
     * @return array<string>
     */
    public function messages(): array
    {
        return [
            'verification_code.required' => __p('mfa::phrase.authenticator_code_is_a_required_field'),
            'verification_code.regex'    => __p('mfa::phrase.authenticator_code_is_invalid'),
        ];
    }
}
