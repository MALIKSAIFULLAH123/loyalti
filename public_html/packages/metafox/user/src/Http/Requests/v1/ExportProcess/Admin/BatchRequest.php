<?php

namespace MetaFox\User\Http\Requests\v1\ExportProcess\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\ExportProcessAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class BatchRequest
 */
class BatchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'   => ['required', 'array'],
            'id.*' => ['integer', 'exists:user_export_processes,id'],
        ];
    }
}
