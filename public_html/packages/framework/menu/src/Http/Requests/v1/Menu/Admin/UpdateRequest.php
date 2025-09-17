<?php

namespace MetaFox\Menu\Http\Requests\v1\Menu\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\MenuAdminController::update;
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
    public function rules()
    {
        return [
            'name'          => ['required', 'string'],
            'module_id'     => ['sometimes', 'string', 'nullable'],
            'resource_name' => ['sometimes', 'string', 'nullable'],
            'is_active'     => ['sometimes', 'numeric', 'nullable'],
            'is_mobile'     => ['sometimes', 'numeric', 'nullable'],
            'is_admin'      => ['sometimes', 'numeric', 'nullable'],
        ];
    }
}
