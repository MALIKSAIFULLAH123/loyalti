<?php

namespace MetaFox\Authorization\Http\Requests\v1\Permission\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\UserRole;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\PermissionAdminController::editForm;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class EditFormRequest.
 */
class EditFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role_id'   => ['sometimes', 'numeric', 'min:1'],
            'module_id' => ['sometimes', 'string'],
            'app'       => ['sometimes', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $appName = Arr::get($data, 'app', 'core');

        $data = Arr::add($data, 'module_id', $appName);

        return Arr::add($data, 'role_id', UserRole::NORMAL_USER_ID);
    }
}
