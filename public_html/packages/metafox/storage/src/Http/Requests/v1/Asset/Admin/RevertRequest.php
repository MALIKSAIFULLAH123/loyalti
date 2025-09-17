<?php

namespace MetaFox\Storage\Http\Requests\v1\Asset\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Storage\Http\Controllers\Api\v1\AssetAdminController::revert
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class RevertRequest.
 */
class RevertRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'default_file_id' => ['required', 'numeric'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        if (0 == Arr::get($data, 'default_file_id')) {
            Arr::set($data, 'default_file_id', null);
        }

        return $data;
    }
}
