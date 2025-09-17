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
 * @link \MetaFox\App\Http\Controllers\Api\v1\PackageAdminController::update;
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
    public function rules(): array
    {
        return [
            'title'              => ['required', 'string', 'min:1', 'max:32'],
            'description'        => ['sometimes'],
            'version'            => ['sometimes'],
            'internal_url'       => ['sometimes'],
            'admin_internal_url' => ['sometimes'],
            'frontend'           => ['sometimes'],
            'author'             => ['sometimes'],
            'author_url'         => ['sometimes'],
        ];
    }
}
