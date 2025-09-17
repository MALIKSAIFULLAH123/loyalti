<?php

namespace MetaFox\Report\Http\Requests\v1\ReportItem;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Report\Http\Controllers\Api\v1\ReportItemController::createForm;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CreateFormRequest.
 */
class CreateFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id'   => ['required', 'numeric', 'min:1'],
            'item_type' => ['required', 'string'],
        ];
    }
}
