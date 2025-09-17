<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class AssignRoleRequest.
 */
class AssignRoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id' => ['required', 'exists:user_entities,id'],
            'roles'   => ['required', 'array'],
            'roles.*' => ['required', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['user_id'] = (int) $data['user_id'];

        return $data;
    }
}
