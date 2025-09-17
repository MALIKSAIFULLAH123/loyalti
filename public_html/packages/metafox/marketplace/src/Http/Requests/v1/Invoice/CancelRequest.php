<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

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
 * Class CancelRequest.
 */
class CancelRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'listing_id' => ['sometimes', 'nullable', 'numeric', new ExistIfGreaterThanZero('exists:marketplace_listings,id')],
        ];
    }
}
