<?php

namespace MetaFox\Featured\Http\Requests\v1\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Rules\ToDateRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Featured\Http\Controllers\Api\v1\ItemController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
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
            'item_type'  => ['sometimes', 'nullable', 'string'],
            'package_id' => ['sometimes', 'nullable', 'integer', 'exists:featured_packages,id'],
            'status'     => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getSearchItemStatusOptions(), 'value'))],
            'from_date' => ['sometimes', 'nullable', 'date'],
            'to_date'   => ['sometimes', 'nullable', 'date', new ToDateRule()],
            'limit'     => ['sometimes', 'nullable', 'integer', 'min:1', 'max:50'],
            'package_duration_period' => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getDurationOptionsForSearch(), 'value'))],
            'id' => ['sometimes', 'integer', 'exists:featured_items,id'],
            'pricing' => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getPricingOptions(), 'value'))],
        ];
    }

	public function validated($key = null, $default = null)
	{
		$data = parent::validated($key, $default);

		$data = Feature::handleRequestEmptyStringValue($data, 'item_type');

		$data = Feature::handleRequestEmptyIntegerValue($data, 'package_id');

        $data = Feature::handleRequestEmptyIntegerValue($data, 'id');

		$data = Feature::handleRequestEmptyStringValue($data, 'status');

		$data = Feature::handleRequestEmptyStringValue($data, 'from_date');

		$data = Feature::handleRequestEmptyStringValue($data, 'to_date');

        $data = Feature::handleRequestEmptyStringValue($data, 'pricing');

		return Feature::handleRequestEmptyStringValue($data, 'package_duration_period');
	}
}
