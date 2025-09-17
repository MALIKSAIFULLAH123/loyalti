<?php

namespace MetaFox\Mfa\Http\Requests\v1\UserService;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Mfa\Http\Controllers\Api\v1\UserServiceController::confirm
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ActivateRequest.
 */
class ActivateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function rules()
    {
        return [
            'service'           => ['string', 'required', new AllowInRule(Mfa::getAllowedServices())],
            'verification_code' => ['required', 'string', 'regex:/[0-9]{6}$/'], // TODO: extend for multiple services
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
