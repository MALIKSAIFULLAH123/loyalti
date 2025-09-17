<?php

namespace MetaFox\Localize\Http\Requests\v1\Language\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Localize\Http\Controllers\Api\v1\LanguageAdminController::update;
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
            '--title'     => ['sometimes', 'string'],
            '--direction' => ['sometimes', 'string', new AllowInRule(['ltr', 'rtl'])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (Arr::has($data, '--title')) {
            Arr::set($data, 'name_alias', $data['--title']);
        }

        if (Arr::has($data, '--direction')) {
            Arr::set($data, 'direction', $data['--direction']);
        }

        return $data;
    }
}
