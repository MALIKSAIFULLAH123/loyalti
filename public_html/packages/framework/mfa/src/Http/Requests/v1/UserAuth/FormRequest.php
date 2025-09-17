<?php

namespace MetaFox\Mfa\Http\Requests\v1\UserAuth;

use Illuminate\Foundation\Http\FormRequest as HttpFormRequest;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Mfa\Http\Controllers\Api\v1\UserAuthController::form
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class FormRequest.
 */
class FormRequest extends HttpFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'mfa_token'  => ['required', 'string', 'exists:mfa_user_auth_tokens,value'],
            'service'    => ['sometimes', 'nullable', 'string', new AllowInRule(Mfa::getAllowedServices())],
            'return_url' => ['sometimes', 'string'],
        ];
    }
}
