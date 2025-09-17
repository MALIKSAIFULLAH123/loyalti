<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\User\Support\Facades\UserVerify;
use MetaFox\User\Support\UserVerifySupport;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ResendVerificationRequest.
 */
class ResendVerificationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', new AllowInRule(UserVerify::getAllowedActions(UserVerifySupport::ADMIN_SERVICE))],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated();
    }
}
