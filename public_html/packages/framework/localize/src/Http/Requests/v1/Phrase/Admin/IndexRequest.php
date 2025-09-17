<?php

namespace MetaFox\Localize\Http\Requests\v1\Phrase\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Localize\Support\Browse\Scopes\Phrase\ViewScope;
use MetaFox\Platform\Facades\Settings;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Localize\Http\Controllers\Api\v1\PhraseAdminController::index;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q'          => ['sometimes', 'nullable', 'string'],
            'group'      => ['sometimes', 'nullable', 'string'],
            'namespace'  => ['sometimes', 'nullable', 'string'],
            'package_id' => ['sometimes', 'nullable', 'string'],
            'locale'     => ['sometimes', 'nullable', 'string'],
            'page'       => ['sometimes', 'numeric', 'min:1'],
            'limit'      => ['sometimes', 'numeric', 'min:10'],
            'view'       => ViewScope::rules(),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!array_key_exists('q', $data) || ($data['q'] == null)) {
            $data['q'] = '';
        }

        if (!array_key_exists('group', $data) || ($data['group'] == null)) {
            $data['group'] = '';
        }

        $data = Arr::add($data, 'locale', Settings::get('localize.default_locale', 'en'));

        return $data;
    }
}
