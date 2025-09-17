<?php

namespace MetaFox\Featured\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Rules\ToDateRule;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Featured\Http\Controllers\Api\v1\InvoiceController::index
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
            'item_type'       => ['sometimes', 'nullable', 'string'],
            'package_id'      => ['sometimes', 'nullable', 'integer', 'exists:featured_packages,id'],
            'status'          => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getSearchInvoiceStatusOptions(), 'value'))],
            'payment_gateway' => ['sometimes', 'nullable', 'integer', 'exists:payment_gateway,id'],
            'from_date'       => ['sometimes', 'nullable', 'date'],
            'to_date'         => ['sometimes', 'nullable', 'date', new ToDateRule()],
            'limit'           => ['sometimes', 'nullable', 'integer', 'min:1', 'max:50'],
            'id'              => ['sometimes', 'nullable', 'integer', 'min:1'],
            'transaction_id'  => ['sometimes', 'nullable', 'string'],
            'q'               => ['sometimes', 'nullable', 'string']
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Feature::handleRequestEmptyStringValue($data, 'item_type');

        $data = Feature::handleRequestEmptyIntegerValue($data, 'package_id');

        $data = Feature::handleRequestEmptyStringValue($data, 'status');

        $data = Feature::handleRequestEmptyIntegerValue($data, 'payment_gateway');

        $data = Feature::handleRequestEmptyIntegerValue($data, 'id');

        $data = Feature::handleRequestEmptyStringValue($data, 'from_date');

        $data = Feature::handleRequestEmptyStringValue($data, 'transaction_id');

        $data = Feature::handleRequestEmptyStringValue($data, 'q');

        $data = Feature::handleRequestEmptyStringValue($data, 'to_date');

        if (!MetaFox::isMobile()) {
            return $data;
        }

        if ($transactionId = Arr::get($data, 'q')) {
            Arr::set($data, 'transaction_id', $transactionId);
        }

        return $data;
    }
}
