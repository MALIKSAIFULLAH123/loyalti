<?php

namespace MetaFox\User\Http\Requests\v1\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\AccountController::getNotificationSetting
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class GetNotificationSettingRequest.
 */
class GetNotificationSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'channel' => ['sometimes', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['channel'])) {
            $data['channel'] = 'mail';
        }

        return $data;
    }
}
