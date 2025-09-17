<?php

namespace MetaFox\Mfa\Http\Requests\v1\UserService;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\MetaFoxPasswordValidationRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Mfa\Http\Controllers\Api\v1\UserServiceController::password
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ConfirmPasswordRequest.
 */
class ConfirmPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function rules()
    {
        $context = user();

        return [
            'service'    => ['string', 'required', new AllowInRule(Mfa::getAllowedServices())],
            'resolution' => ['string', 'sometimes', 'nullable', new AllowInRule(['web', 'mobile'])],
            'password'   => ['required', 'string', new MetaFoxPasswordValidationRule($context)],
        ];
    }
}
