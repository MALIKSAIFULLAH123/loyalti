<?php

namespace MetaFox\Featured\Http\Requests\v1\Invoice\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Rules\ToDateRule;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Featured\Http\Controllers\Api\v1\InvoiceAdminController::index
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
            'full_name'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'limit'           => ['sometimes', 'nullable', 'integer', 'min:1', 'max:50'],
            'payment_gateway' => ['sometimes', 'nullable', 'integer', 'exists:payment_gateway,id'],
            'item_type'       => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getApplicableItemTypeOptions(), 'value'))],
            'package_id'      => ['sometimes', 'nullable', 'integer', new AllowInRule(array_column(Feature::getPackageSearchOptions(), 'value'))],
            'status'          => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getSearchInvoiceStatusOptions(), 'value'))],
            'transaction_id'  => ['sometimes', 'nullable', 'string'],
            'from_date'       => ['sometimes', 'nullable', 'date'],
            'to_date'         => ['sometimes', 'nullable', 'date', new ToDateRule()],
            'id'              => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Feature::handleRequestEmptyStringValue($data, 'full_name');

        $data = Feature::handleRequestEmptyStringValue($data, 'item_type');

        $data = Feature::handleRequestEmptyStringValue($data, 'status');

        $data = Feature::handleRequestEmptyStringValue($data, 'transaction_id');

        $data = Feature::handleRequestEmptyStringValue($data, 'from_date');

        $data = Feature::handleRequestEmptyStringValue($data, 'to_date');

        $data = Feature::handleRequestEmptyIntegerValue($data, 'package_id');

        $data = Feature::handleRequestEmptyIntegerValue($data, 'id');

        return Feature::handleRequestEmptyIntegerValue($data, 'payment_gateway');
    }
}
