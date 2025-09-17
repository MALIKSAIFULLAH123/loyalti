<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Marketplace\Http\Controllers\Api\v1\InvoiceController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ChangeRequest.
 */
class ChangeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id' => ['required', 'numeric', 'exists:marketplace_invoices,id'],
        ];
    }
}
