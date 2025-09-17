<?php

namespace Foxexpert\Sevent\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Sevent\Http\Controllers\Api\v1\InvoiceController::store
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
            'id'              => ['required', 'numeric'],
            'seventAmount' => ['nullable', 'numeric'],
            'payment_gateway' => ['required', 'numeric', 'exists:payment_gateway,id'],
        ];
    }
}
