<?php

namespace MetaFox\SEO\Http\Requests\v1\Meta\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\MetaAdminController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateSchemaRequest.
 */
class UpdateSchemaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'schema' => ['sometimes', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (Arr::has($data, 'schema')) {
            Arr::set($data, 'schema', json_decode($data['schema']));
        }

        return $data;
    }
}
