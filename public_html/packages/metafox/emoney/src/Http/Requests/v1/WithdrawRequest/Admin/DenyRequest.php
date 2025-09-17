<?php

namespace MetaFox\EMoney\Http\Requests\v1\WithdrawRequest\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\EMoney\Http\Controllers\Api\v1\WithdrawRequestAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class DenyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'     => ['required', 'integer', 'exists:emoney_withdraw_requests,id'],
            'reason' => ['required', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['reason'] = parse_input()->clean($data['reason']);

        return $data;
    }
}
