<?php

namespace MetaFox\Advertise\Http\Requests\v1\Invoice\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Advertise\Http\Controllers\Api\v1\InvoiceAdminController::index
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
            'start_date'     => ['sometimes', 'nullable', 'string'],
            'end_date'       => ['sometimes', 'nullable', 'string'],
            'full_name'      => ['sometimes', 'nullable', 'string'],
            'payment_status' => ['sometimes', 'string', new AllowInRule(Support::getInvoiceStatuses())],
            'limit'          => ['sometimes', 'numeric', 'min:1'],
            'page'           => ['sometimes', 'numeric', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        }

        return $data;
    }
}
