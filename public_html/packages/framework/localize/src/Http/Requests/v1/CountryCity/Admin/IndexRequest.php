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
 * @link \MetaFox\Localize\Http\Controllers\Api\v1\CountryCityAdminController::index
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
    public function rules()
    {
        return [
            'q'        => 'sometimes|string|nullable',
            'state_id' => 'int|required',
            'page'     => 'sometimes|int|nullable',
            'limit'    => 'sometimes|int|nullable',
        ];
    }
}
