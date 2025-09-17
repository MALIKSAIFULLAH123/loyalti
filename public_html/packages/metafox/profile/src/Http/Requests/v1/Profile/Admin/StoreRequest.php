<?php

namespace MetaFox\Profile\Http\Requests\v1\Profile\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Profile\Http\Controllers\Api\v1\ProfileAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
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
            'user_type'    => ['string', 'required'],
            'profile_type' => [
                'string', 'required', 'regex:/^([a-z]+)$/',
                new CaseInsensitiveUnique('user_custom_profiles', 'profile_type'),
            ],
            'title' => ['string', 'required'],
        ];
    }
}
