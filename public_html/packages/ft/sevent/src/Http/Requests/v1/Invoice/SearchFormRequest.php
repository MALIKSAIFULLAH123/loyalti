<?php

namespace Foxexpert\Sevent\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Foxexpert\Sevent\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------
 * This class is used by automatic dependency injection:
 * @link \Foxexpert\Sevent\Http\Controllers\Api\v1\InvoiceController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class SearchFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'view'       => ['sometimes', new AllowInRule(ViewScope::getAllowView())],
            'sevent_id' => ['sometimes', 'numeric', 'exists:sevents,id'],
            'q'          => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}
