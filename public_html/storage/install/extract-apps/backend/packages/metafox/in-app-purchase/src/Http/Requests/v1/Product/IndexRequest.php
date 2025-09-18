<?php

namespace MetaFox\InAppPurchase\Http\Requests\v1\Product;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

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
            'q'         => ['sometimes', 'nullable', 'string'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
            'item_type' => ['sometimes', 'nullable', 'string'],
            'page'      => ['sometimes', 'numeric', 'min:1'],
        ];
    }
}
