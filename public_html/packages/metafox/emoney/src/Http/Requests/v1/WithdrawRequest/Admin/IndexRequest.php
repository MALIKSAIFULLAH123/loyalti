<?php

namespace MetaFox\EMoney\Http\Requests\v1\WithdrawRequest\Admin;

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
 * @link \MetaFox\EMoney\Http\Controllers\Api\v1\WithdrawRequestAdminController::index
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
            'creator'   => ['sometimes', 'nullable', 'string'],
            'from_date' => ['sometimes', 'nullable', 'string'],
            'to_date'   => ['sometimes', 'nullable', 'string'],
            'status'    => ['sometimes', 'nullable', new AllowInRule(Emoney::getRequestStatuses())],
            'id'        => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
