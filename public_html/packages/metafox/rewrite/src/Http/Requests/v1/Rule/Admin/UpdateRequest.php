<?php

namespace MetaFox\Rewrite\Http\Requests\v1\Rule\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\RuleAdminController::update;
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
            'from_path'      => ['required', 'string'],
            'to_path'        => ['required', 'string'],
            'to_mobile_path' => ['required', 'string'],
            'module_id'      => ['sometimes'],
        ];
    }
}
