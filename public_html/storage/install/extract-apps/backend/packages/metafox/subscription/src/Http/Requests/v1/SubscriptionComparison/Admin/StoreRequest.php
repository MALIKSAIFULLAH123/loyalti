<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionComparison\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionComparisonAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'title' => ['required', 'string', 'between:1,' . Helper::MAX_COMPARISON_TITLE_LENGTH],
        ];

        $rules = array_merge($rules, $this->addPackageRules());

        return $rules;
    }

    protected function addPackageRules(): array
    {
        $rules = [];

        $context = user();

        $packages = resolve(SubscriptionPackageRepositoryInterface::class)->viewPackages($context, ['view' => Helper::VIEW_ADMINCP]);

        if (null !== $packages && $packages->count()) {
            foreach ($packages as $package) {
                $varName = 'package_' . $package->entityId() . '_';

                $varNameType = $varName . 'type';

                Arr::set($rules, $varNameType, ['required', 'string', new AllowInRule(Helper::getComparisonTypes())]);

                Arr::set($rules, $varName . 'text', ['nullable', 'required_if:' . $varNameType . ',' . Helper::COMPARISON_TYPE_TEXT, 'string', 'between:1,' . Helper::MAX_LENGTH_FOR_COMPARISON_TEXT]);
            }
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $packages = [];

        foreach ($data as $key => $value) {
            if (preg_match('/package_(\d+)_(.*)/', $key, $matches)) {
                Arr::set($packages, $matches[1] . '.' . $matches[2], $value);
            }
        }

        Arr::set($data, 'packages', $packages);

        return $data;
    }
}
