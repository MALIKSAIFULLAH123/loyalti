<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::index
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
            'limit' => ['sometimes', 'numeric', 'min:' . Pagination::DEFAULT_MIN_ITEM_PER_PAGE],
            'page'  => ['sometimes', 'numeric', 'min:1'],
        ];
    }
}
