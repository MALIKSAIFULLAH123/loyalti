<?php

namespace MetaFox\InAppPurchase\Http\Requests\v1\Product;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\InAppPurchase\Http\Controllers\Api\v1\ProductController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'ios_product_id'     => ['sometimes'],
            'android_product_id' => ['sometimes'],
        ];
    }
}
