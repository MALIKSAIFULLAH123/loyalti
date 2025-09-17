<?php

namespace MetaFox\Rad\Http\Requests\v1\Code\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makeForm;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class MakeFormRequest.
 */
class MakeFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'package'     => ['sometimes', 'string'],
            '--name'      => ['sometimes', 'string', 'min:3'],
            '--action'    => ['sometimes', 'string'],
            '--overwrite' => ['sometimes', 'boolean'],
            '--test'      => ['sometimes', 'boolean'],
            '--ver'       => ['sometimes', 'string'],
            '--admin'     => ['sometimes', 'boolean'],
            '--dry'       => ['sometimes', 'boolean'],
            '--request'   => ['sometimes', 'boolean'],
        ];
    }
}
