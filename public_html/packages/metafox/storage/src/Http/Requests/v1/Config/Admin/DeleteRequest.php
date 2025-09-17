<?php

namespace MetaFox\Storage\Http\Requests\v1\Config\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Storage\Http\Controllers\Api\v1\ConfigAdminController::delete
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class DeleteRequest
 */
class DeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'         => ['required', 'string'],
            'confirm_name' => ['required', 'string', 'same:name'],
            'is_remove'    => ['required', 'boolean'],
        ];
    }
}
