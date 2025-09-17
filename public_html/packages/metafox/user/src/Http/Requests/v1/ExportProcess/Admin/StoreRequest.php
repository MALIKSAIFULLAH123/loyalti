<?php

namespace MetaFox\User\Http\Requests\v1\ExportProcess\Admin;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\ExportProcessAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'properties' => ['sometimes', 'array', 'min:1'],
            'filters'    => ['array'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data    = parent::validated($key, $default);
        $results = [];

        if (!Arr::has($data, 'properties')) {
            return $data;
        }

        foreach ($data['properties'] as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, $value);
            }
        }

        Arr::set($data, 'properties', $results);
        return $data;
    }
}
