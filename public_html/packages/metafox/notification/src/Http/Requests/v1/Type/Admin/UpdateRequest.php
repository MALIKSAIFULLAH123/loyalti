<?php

namespace MetaFox\Notification\Http\Requests\v1\Type\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Notification\Http\Controllers\Api\v1\TypeAdminController::update;
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
            'can_edit'   => ['sometimes', 'boolean'],
            'is_active'  => ['sometimes', 'boolean'],
            'is_system'  => ['sometimes', 'boolean'],
            'is_request' => ['sometimes', 'boolean'],
            'database'   => ['sometimes', 'boolean'],
            'mail'       => ['sometimes', 'boolean'],
        ];
    }
}
