<?php

namespace MetaFox\Localize\Http\Requests\v1\Language\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makeLanguage();
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class GenerateLanguageRequest.
 */
class MakeLanguageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            '--vendor'        => ['required', 'string'],
            '--title'         => ['required', 'string'],
            '--language_code' => ['required', 'string', 'unique:core_languages,language_code'],
        ];
    }
}
