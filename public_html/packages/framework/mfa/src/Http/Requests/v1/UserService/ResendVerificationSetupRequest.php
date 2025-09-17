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
 * @link \MetaFox\Mfa\Http\Controllers\Api\v1\UserServiceController::resendVerification()
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ResendVerificationRequest.
 */
class ResendVerificationSetupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'service' => ['required', 'string', new AllowInRule(Mfa::getAllowedServices())],
            'action'  => ['required', 'string', new AllowInRule(Mfa::getAllowedAction())],
        ];
    }
}
