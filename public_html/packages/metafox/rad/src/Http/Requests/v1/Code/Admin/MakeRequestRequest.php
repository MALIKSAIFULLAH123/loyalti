<?php

namespace MetaFox\Rad\Http\Requests\v1\Code\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class MakeRequestRequest.
 * @codeCoverageIgnore
 * @ignore
 * @link \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makeRequest()
 */
class MakeRequestRequest extends FormRequest
{
    public function rules()
    {
        return [
            'package'     => ['sometimes', 'string'],
            '--name'      => ['sometimes', 'string', 'min:3'],
            '--action'    => ['sometimes', 'string'],
            '--overwrite' => ['sometimes', 'boolean'],
            '--test'   => ['sometimes', 'boolean'],
            '--ver'       => ['sometimes', 'string'],
            '--admin'     => ['sometimes', 'boolean'],
            '--dry'       => ['sometimes', 'boolean'],
        ];
    }
}
