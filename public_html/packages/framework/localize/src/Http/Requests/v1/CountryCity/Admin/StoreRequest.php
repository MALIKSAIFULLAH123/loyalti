<?php

namespace MetaFox\Localize\Http\Requests\v1\CountryCity\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Localize\Http\Controllers\Api\v1\CountryCityAdminController::store
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
            'name'       => ['required', 'string'],
            'city_code'  => ['required', 'integer', 'unique:core_country_cities,city_code'],
            'state_code' => ['required', 'numeric', 'exists:core_country_states,state_code'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'city_code.unique' => __p('localize::phrase.the_city_code_already_existed'),
        ];
    }
}
