<?php

namespace MetaFox\User\Http\Requests\v1\InactiveProcess\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'owner_ids'   => ['required', 'array'],
            'owner_ids.*' => ['exists:users,id'],
            'round'       => ['sometimes', 'nullable', 'numeric'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return $data;
    }
}
