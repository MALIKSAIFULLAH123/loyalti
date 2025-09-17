<?php

namespace MetaFox\Mfa\Http\Requests\v1\UserAuth;

use Illuminate\Foundation\Http\FormRequest as HttpFormRequest;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Mfa\Http\Controllers\Api\v1\UserAuthController::removeAuthentication()
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class RemoveAuthenticationRequest.
 */
class RemoveAuthenticationRequest extends HttpFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id'    => ['required', 'numeric', 'exists:user_entities,id'],
            'services'   => ['required', 'array'],
            'services.*' => ['required', 'string', new AllowInRule(Mfa::getAllowedServices())],
        ];
    }
}
