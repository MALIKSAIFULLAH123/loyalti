<?php

namespace MetaFox\Authorization\Http\Requests\v1\Role\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\RoleAdminController::store;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'           => [
                'required',
                'string',
                'between:3,255',
                new CaseInsensitiveUnique('auth_roles', 'name'),
            ],
            'inherited_role' => ['required', 'exists:MetaFox\Authorization\Models\Role,id', 'numeric'],
        ];
    }

    /**
     * @param  string|null          $key
     * @param  mixed                $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data['parent_id'] = Arr::get($data, 'inherited_role', 0);

        return $data;
    }
}
