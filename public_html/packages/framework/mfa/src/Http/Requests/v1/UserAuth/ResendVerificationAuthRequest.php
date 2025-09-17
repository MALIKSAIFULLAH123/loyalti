<?php

namespace MetaFox\Mfa\Http\Requests\v1\UserAuth;

use MetaFox\Mfa\Http\Requests\v1\UserService\ResendVerificationSetupRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Mfa\Http\Controllers\Api\v1\UserAuthController::resendVerificationAuth()
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ResendVerificationAuthRequest.
 */
class ResendVerificationAuthRequest extends ResendVerificationSetupRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules['password'] = ['required', 'string', 'exists:mfa_user_auth_tokens,value'];

        return $rules;
    }
}
