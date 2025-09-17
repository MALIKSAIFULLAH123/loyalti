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
 * @link \MetaFox\Core\Http\Controllers\Api\v1\ModuleAdminController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class MakeModelRequest.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeModelRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'package'           => ['sometimes', 'string'],
            '--name'            => ['sometimes', 'string', 'min:3'],
            '--content'         => ['sometimes', 'boolean'],
            '--overwrite'       => ['sometimes', 'boolean'],
            '--version'         => ['sometimes', 'string'],
            '--table'           => ['sometimes', 'string'],
            '--entity'          => ['sometimes', 'string'],
            '--has-category' => ['sometimes', 'boolean'],
            '--has-factory'      => ['sometimes', 'boolean'],
            '--has-repository'   => ['sometimes', 'boolean'],
            '--has-text'     => ['sometimes', 'boolean'],
            '--has-tag'      => ['sometimes', 'boolean'],
            '--has-policy'       => ['sometimes', 'boolean'],
            '--has-privacy'      => ['sometimes', 'boolean'],
            '--has-observer'     => ['sometimes', 'boolean'],
            '--dry'       => ['sometimes', 'boolean'],
        ];
    }
}
