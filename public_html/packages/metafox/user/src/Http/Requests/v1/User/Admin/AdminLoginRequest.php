<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserController::loginPopupForm;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class AdminLoginRequest.
 */
class AdminLoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username'      => ['required'],
            'password'      => ['required'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (isset($key)) {
            // retrieving by key
            return $data;
        }

        Arr::set($data, 'resolution', MetaFoxConstant::RESOLUTION_ADMIN);

        return $data;
    }
}
