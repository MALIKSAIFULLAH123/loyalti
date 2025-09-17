<?php

namespace MetaFox\Featured\Http\Requests\v1\Package;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Featured\Http\Controllers\Api\v1\PackageController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'view' => ['required', new AllowInRule([Browse::VIEW_SEARCH])],
            'q'    => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $q = Arr::get($data, 'q');

        if (!is_string($q)) {
            return $data;
        }

        $q = trim($q);

        if (MetaFoxConstant::EMPTY_STRING !== $q) {
            return $data;
        }

        Arr::forget($data, 'q');

        return $data;
    }
}
