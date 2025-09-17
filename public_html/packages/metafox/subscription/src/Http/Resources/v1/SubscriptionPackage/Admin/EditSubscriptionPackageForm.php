<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\Section;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as Facade;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditSubscriptionPackageForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditSubscriptionPackageForm extends CreateSubscriptionPackageForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;

        $upgradedPackageIds = $downgradedPackageId = $description = null;

        if (null !== $resource->description) {
            $description = $resource->description->text_parsed;
        }

        if (null !== $resource->upgradedPackages && $resource->upgradedPackages->count()) {
            $upgradedPackageIds = $resource->upgradedPackages->toArray();
            $upgradedPackageIds = Arr::pluck($upgradedPackageIds, 'dependency_package_id');
        }

        if (null !== $resource->downgradedPackage) {
            $downgradedPackageId = $resource->downgradedPackage->dependency_package_id;
        }

        $isActive = Helper::STATUS_ACTIVE == $resource->status;

        $allowRenewTypes = $resource->getAllowedRenewTypes();

        $recurringPeriod = $resource->recurring_period;

        if (!$resource->is_recurring) {
            if (null === $allowRenewTypes) {
                $allowRenewTypes = array_column(Helper::getAllowedRenewMethod(), 'value');
            }

            if (null === $recurringPeriod) {
                $recurringPeriod = Helper::RECURRING_PERIOD_MONTHLY;
            }
        }

        $values = [
            'id'                                            => $resource->entityId(),
            'title'                                         => $resource->title,
            'text'                                          => $description,
            'upgraded_role_id'                              => $resource->upgraded_role_id,
            'is_on_registration'                            => (int) $resource->is_on_registration,
            'is_active'                                     => (int) $isActive,
            'upgraded_package_id'                           => $upgradedPackageIds,
            'downgraded_package_id'                         => $downgradedPackageId,
            'is_free'                                       => (int) $resource->is_free,
            'is_recurring'                                  => (int) $resource->is_recurring,
            'allowed_renew_type'                            => $allowRenewTypes,
            'days_notification_before_subscription_expired' => $resource->days_notification_before_subscription_expired,
            'recurring_period'                              => $recurringPeriod,
            'background_color_for_comparison'               => $resource->background_color_for_comparison,
            'recurring_price'                               => $resource->getRecurringPrices(),
            'price'                                         => $resource->getPrices(),
        ];

        $visibleRoles = null;

        if (null !== $resource->visible_roles) {
            $visibleRoles = json_decode($resource->visible_roles, true);
        }

        if (null === $resource->visible_roles) {
            $roles = $this->getRolesForVisibility();

            if (is_array($roles)) {
                $visibleRoles = Arr::pluck($roles, 'value');
            }
        }

        if (is_array($visibleRoles)) {
            Arr::set($values, 'visible_roles', $visibleRoles);
        }

        $this->title(__p('subscription::admin.edit_package'))
            ->action(apiUrl('admin.subscription.package.update', [
                'package' => $resource->entityId(),
            ]))
            ->asPut()
            ->setValue($values);
    }

    public function boot(SubscriptionPackageRepositoryInterface $repository, int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function hasDisableFields(): bool
    {
        return Facade::hasDisableFields($this->resource?->entityId());
    }

    protected function setFooterFields(Section $footer): void
    {
        $this->addDefaultFooter(true);
    }

    protected function getEnablePriceField(array $currencies): array
    {
        $recurringCurrencies = array_keys($this->resource->getRecurringPrices() ?? []);
        $currencies          = array_values(Arr::pluck($currencies, 'value'));

        return array_diff($currencies, $recurringCurrencies);
    }
}
