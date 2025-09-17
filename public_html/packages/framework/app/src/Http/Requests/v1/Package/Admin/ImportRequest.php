<?php

namespace MetaFox\App\Http\Requests\v1\Package\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\App\Http\Controllers\Api\v1\PackageAdminController::importForm;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ImportRequest.
 */
class ImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
        ];
    }
}
