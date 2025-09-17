<?php

namespace MetaFox\User\Http\Requests\v1\Account;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\AccountController::updateNotificationSetting
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateNotificationSettingRequest.
 */
class UpdateNotificationSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'module_id' => ['sometimes', 'string'],
            'value'     => ['sometimes', 'int', new AllowInRule([0, 1])],
            'var_name'  => ['sometimes', 'string'],
            'channel'   => ['required', 'string'],
        ];
    }
}
