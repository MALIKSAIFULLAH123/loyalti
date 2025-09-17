<?php

namespace MetaFox\User\Http\Requests\v1\InactiveProcess\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\InactiveProcessAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class BatchProcessMailingRequest
 */
class BatchProcessMailingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'   => ['required', 'array'],
            'id.*' => ['exists:users,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'owner_ids', Arr::get($data, 'id'));

        return $data;
    }
}
