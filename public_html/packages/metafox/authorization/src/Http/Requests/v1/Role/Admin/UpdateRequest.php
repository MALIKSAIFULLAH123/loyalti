<?php

namespace MetaFox\Authorization\Http\Requests\v1\Role\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\RoleAdminController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $roleId = (int) ($this->route('role'));

        return [
            'name' => [
                'required',
                'string',
                'between:3,255',
                new CaseInsensitiveUnique('auth_roles', 'name', $roleId),
            ],
        ];
    }
}
