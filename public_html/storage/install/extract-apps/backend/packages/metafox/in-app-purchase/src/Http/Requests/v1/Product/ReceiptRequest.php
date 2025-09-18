<?php

namespace MetaFox\InAppPurchase\Http\Requests\v1\Product;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\InAppPurchase\Support\Constants;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\InAppPurchase\Http\Controllers\Api\v1\ProductController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ReceiptRequest.
 */
class ReceiptRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'transaction_id'  => ['string', 'nullable', 'required_without:subscription_id'], // ios
            'subscription_id' => ['string', 'nullable', 'required_without:transaction_id'], // android
            'purchase_token'  => ['string', 'nullable', 'required_without:transaction_id'], // android
            'platform'        => ['string', 'required', 'in:' . Constants::IOS . ',' . Constants::ANDROID],
            'gateway_token'   => ['string', 'sometimes', 'nullable'], // payment_order id
            'item_id'         => ['numeric', 'sometimes', 'nullable'],
            'item_type'       => ['string', 'sometimes', 'nullable'],
        ];
    }
}
