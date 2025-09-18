<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin;

use Illuminate\Support\Arr;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Rules\DowngradePackage;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as Facade;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionPackageAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    protected function hasDisabledFields(): bool
    {
        $id = request()->input('id');

        return Facade::hasDisableFields($id);
    }

    protected function getDependencyPackageRules(): array
    {
        $id = request()->input('id');

        return [
            'upgraded_package_id'   => ['sometimes', 'nullable', 'array'],
            'upgraded_package_id.*' => ['numeric', 'exists:subscription_packages,id', 'not_in:' . $id],
            'downgraded_package_id' => ['sometimes', 'nullable', new DowngradePackage(), 'not_in:' . $id],
        ];
    }

    public function messages(): array
    {
        $messages = array_merge(parent::messages(), [
            'upgraded_package_id.*.not_in' => __p('subscription::admin.you_can_not_choose_current_package_for_upgraded_packages'),
            'downgraded_package_id.not_in' => __p('subscription::admin.you_can_not_choose_current_package_for_downgraded_package'),
        ]);

        return $messages;
    }

    protected function getEnablePriceField(array $currencies): array
    {
        $id                  = request()->input('id');
        $package             = resolve(SubscriptionPackageRepositoryInterface::class)->find($id);
        $recurringCurrencies = array_keys($package->getRecurringPrices() ?? []);
        $currencies          = array_values(Arr::pluck($currencies, 'value'));

        return array_diff($currencies, $recurringCurrencies);
    }
}
