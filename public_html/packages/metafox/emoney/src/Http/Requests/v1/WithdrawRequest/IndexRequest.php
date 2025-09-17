<?php

namespace MetaFox\EMoney\Http\Requests\v1\WithdrawRequest;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\EMoney\Http\Controllers\Api\v1\WithdrawRequestController::index
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
            'from_date' => ['sometimes', 'nullable', 'string'],
            'to_date'   => ['sometimes', 'nullable', 'string'],
            'status'    => ['sometimes', 'nullable', new AllowInRule(Emoney::getRequestStatuses())],
            'id'        => ['sometimes', 'integer', 'exists:emoney_withdraw_requests'],
        ];
    }

    public function messages()
    {
        return [
            'id.exists' => __p('ewallet::validation.the_request_with_this_id_does_not_exist'),
        ];
    }
}
