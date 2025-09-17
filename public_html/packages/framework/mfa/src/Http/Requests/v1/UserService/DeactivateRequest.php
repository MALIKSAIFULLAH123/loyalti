<?php

namespace MetaFox\Mfa\Http\Requests\v1\UserService;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\MetaFoxPasswordValidationRule;

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
 * Class DeactivateRequest.
 */
class DeactivateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $context = user();

        $rules = [
            'service' => ['string', 'required', new AllowInRule(Mfa::getAllowedServices())],
        ];

        if (Mfa::hasConfirmPassword($context)) {
            $rules['password'] = ['required', 'string', new MetaFoxPasswordValidationRule($context)];
        }

        return $rules;
    }
}
