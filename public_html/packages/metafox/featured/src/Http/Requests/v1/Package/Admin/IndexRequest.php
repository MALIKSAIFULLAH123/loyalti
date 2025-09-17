<?php

namespace MetaFox\Featured\Http\Requests\v1\Package\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Support\Constants;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Featured\Http\Controllers\Api\v1\PackageAdminController::index
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
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'duration_period' => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getDurationOptionsForSearch(), 'value'))],
            'pricing' => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getPricingOptions(), 'value'))],
            'status'  => ['sometimes', 'nullable', new AllowInRule(array_column(Feature::getStatusOptions(), 'value'))],
            'limit'   => ['sometimes', 'integer', 'min:1', 'max:' . Pagination::DEFAULT_MAX_ITEM_PER_PAGE],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handleTitle($data);

        $data = $this->handleDurationPeriod($data);

        $data = $this->handlePricing($data);

        return $this->handleStatus($data);
    }

    protected function handleStatus(array $data): array
    {
        $option = Arr::get($data, 'status');

        if (null === $option) {
            return $data;
        }

        if (Constants::STATUS_OPTION_ACTIVE === $option) {
            return array_merge($data, [
                'is_active' => true,
            ]);
        }

        return array_merge($data, [
            'is_active' => false,
        ]);
    }

    protected function handlePricing(array $data): array
    {
        $option = Arr::get($data, 'pricing');

        if (null === $option) {
            return $data;
        }

        if (Constants::PRICING_OPTION_FREE === $option) {
            return array_merge($data, [
                'is_free' => true,
            ]);
        }

        return array_merge($data, [
            'is_free' => false,
        ]);
    }

    protected function handleDurationPeriod(array $data): array
    {
        $period = Arr::get($data, 'duration_period');

        if (Constants::DURATION_ENDLESS === $period) {
            Arr::set($data, 'duration_period', null);
        }

        return $data;
    }

    protected function handleTitle(array $data): array
    {
        $title = Arr::get($data, 'title');

        if (is_string($title)) {
            $title = trim($title);
        }

        if (!is_string($title) || MetaFoxConstant::EMPTY_STRING === $title) {
            Arr::forget($data, 'title');
        }

        return $data;
    }
}
