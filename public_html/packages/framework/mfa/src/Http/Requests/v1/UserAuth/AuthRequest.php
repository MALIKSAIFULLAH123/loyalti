<?php

namespace MetaFox\Mfa\Http\Requests\v1\UserAuth;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Mfa\Http\Controllers\Api\v1\UserAuthController::auth
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class AuthRequest.
 */
class AuthRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'service'           => ['required', 'string', new AllowInRule(Mfa::getAllowedServices())],
            'password'          => ['required', 'string', 'exists:mfa_user_auth_tokens,value'],
            'verification_code' => ['required', 'string', 'regex:/[0-9]{6}$/'],
            'return_url'        => ['sometimes', 'string'],
            'remember'          => ['sometimes', 'nullable', new AllowInRule([0, 1])],
        ];
    }

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
