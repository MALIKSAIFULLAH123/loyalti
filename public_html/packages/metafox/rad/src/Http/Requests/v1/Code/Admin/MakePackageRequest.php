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
 * @link \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makePackage()
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class GenerateLanguageRequest.
 * @link \MetaFox\Core\Http\Resources\v1\Code\Admin\MakePackageForm
 */
class MakePackageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'package'         => ['required', 'string'],
            '--vendor'        => ['required', 'string'],
            '--name'          => ['required', 'string'],
            '--author'        => ['required', 'string'],
            '--homepage'      => ['required', 'string'],
            '--dry'           => ['sometimes', 'boolean'],
        ];
    }
}
