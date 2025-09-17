<?php

namespace MetaFox\Payment\Http\Requests\v1\Gateway;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Payment\Http\Controllers\Api\v1\GatewayController::gateway;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class TestModeRequest.
 */
class TestModeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'test_mode' => ['required', 'numeric', 'in:0,1'],
        ];
    }
}
