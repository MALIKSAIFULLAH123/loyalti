<?php

namespace MetaFox\Authorization\Http\Requests\v1\Role\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Models\Role;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\RoleController::updatePermission;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class RemovePermissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function rules(): array
    {
        return [
            'role_id'       => ['required', 'numeric', sprintf('exists:%s,id', Role::class)],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['sometimes', 'string', 'min:1', sprintf('exists:%s,name', Permission::class)],
        ];
    }
}
