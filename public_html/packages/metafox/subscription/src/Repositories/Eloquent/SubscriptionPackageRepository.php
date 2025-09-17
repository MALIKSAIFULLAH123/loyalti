<?php

namespace MetaFox\Subscription\Repositories\Eloquent;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\UserRole;
use MetaFox\Subscription\Jobs\DeletePackage;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Policies\SubscriptionPackagePolicy;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionDependencyPackageRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as Facade;
use MetaFox\Subscription\Support\Helper;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class SubscriptionPackageRepository.
 */
class SubscriptionPackageRepository extends AbstractRepository implements SubscriptionPackageRepositoryInterface
{
    public function model()
    {
        return SubscriptionPackage::class;
    }

    public function getRoleOptions(array $disallowedRoles = []): array
    {
        $repository = resolve(RoleRepositoryInterface::class);

        $options = $repository->getRoleOptions();

        if (null === $options) {
            return [];
        }

        if (count($disallowedRoles)) {
            foreach ($options as $key => $option) {
                if (in_array($option['value'], $disallowedRoles)) {
                    unset($options[$key]);
                }
            }
            $options = array_values($options);
        }

        return $options;
    }

    public function getRoleOptionsForSuccess(): array
    {
        return $this->getRoleOptions(Helper::getDisallowedRolesForSuccess());
    }

    /**
     * @return array
     */
    public function getRoleOptionsForVisibility(): array
    {
        return $this->getRoleOptions(Helper::getDisallowedRolesForVisibility());
    }

    public function getRoleOptionsForDowngrade(): array
    {
        return $this->getRoleOptions(Helper::getDisallowedRolesForDowngrade());
    }

    public function createPackage(User $context, array $attributes): SubscriptionPackage
    {
        /**
         * @var SubscriptionPackage
         */
        $package = $this->getModel()->newInstance();

        $lastestPackage = $this->getModel()->newModelQuery()
            ->where('status', '!=', Helper::STATUS_DELETED)
            ->orderBy('ordering', 'desc')
            ->first();

        $ordering = 0;

        if (null !== $lastestPackage) {
            $ordering = $lastestPackage->ordering;
        }

        $attributes = array_merge($attributes, [
            'price'              => json_encode($attributes['price']),
            'visible_roles'      => null !== $attributes['visible_roles'] ? json_encode($attributes['visible_roles']) : null,
            'recurring_price'    => is_array($attributes['recurring_price']) ? json_encode($attributes['recurring_price']) : null,
            'allowed_renew_type' => is_array($attributes['allowed_renew_type']) ? json_encode($attributes['allowed_renew_type']) : null,
            'ordering'           => ++$ordering,
        ]);

        $attributes = $this->handleLogo($attributes);

        $package->fill($attributes);

        $package->save();

        $id = $package->entityId();

        $dependencyRepository = resolve(SubscriptionDependencyPackageRepositoryInterface::class);

        if (is_array($attributes['upgraded_package_id'])) {
            $dependencyRepository->createMultipleDependencies(
                $context,
                $id,
                $attributes['upgraded_package_id'],
                Helper::DEPENDENCY_UPGRADE
            );
        }

        if (null !== $attributes['downgraded_package_id']) {
            $dependencyRepository->createMultipleDependencies(
                $context,
                $id,
                [$attributes['downgraded_package_id']],
                Helper::DEPENDENCY_DOWNGRADE
            );
        }

        $package->refresh();

        $this->clearCaches();

        return $package;
    }

    protected function handleLogo(array $attributes, ?SubscriptionPackage $package = null): array
    {
        if (Arr::has($attributes, 'remove_image') && $attributes['remove_image']) {
            if (null !== $package) {
                $this->deletePackageLogo($package);
            }
            $attributes = array_merge($attributes, [
                'image_file_id' => null,
            ]);
        }

        if (Arr::has($attributes, 'temp_file') && $attributes['temp_file'] > 0) {
            $tempFile = upload()->getFile($attributes['temp_file']);
            Arr::set($attributes, 'image_file_id', $tempFile->entityId());
            upload()->rollUp($attributes['temp_file']);
        }

        return $attributes;
    }

    public function updatePackage(User $context, int $id, array $attributes): SubscriptionPackage
    {
        $package = $this->find($id);

        $hasDisableFields = $this->hasDisableFields($id);

        $attributes = array_merge($attributes, [
            'price'           => json_encode($attributes['price']),
            'recurring_price' => $this->handleRecurringPrice($package, $attributes),

        ]);

        if (!$hasDisableFields) {
            $attributes['allowed_renew_type'] = is_array($attributes['allowed_renew_type']) ? json_encode($attributes['allowed_renew_type']) : null;
        }

        $attributes = $this->handleLogo($attributes, $package);

        $package->fill($attributes);

        $package->save();

        $upgradedPackageIds = $downgradedPackageIds = [];

        if (is_array($attributes['upgraded_package_id'])) {
            $upgradedPackageIds = $attributes['upgraded_package_id'];
        }

        if (null !== $attributes['downgraded_package_id']) {
            $downgradedPackageIds = [$attributes['downgraded_package_id']];
        }

        $this->updatePackageDependencies($context, $package, $upgradedPackageIds, $downgradedPackageIds);

        $package->refresh();

        $this->clearCaches();

        return $package;
    }

    protected function handleRecurringPrice(SubscriptionPackage $package, array $attributes): ?string
    {
        $recurringPrice = array_merge(
            $package->getRecurringPrices() ?: [],
            Arr::get($attributes, 'recurring_price') ?: []
        );

        if (empty($recurringPrice)) {
            return null;
        }

        return json_encode($recurringPrice);
    }

    protected function updatePackageDependencies(
        User                $context,
        SubscriptionPackage $package,
        array               $upgradedPackageIds,
        array               $downgradedPackageIds
    ): void {
        $dependencyRepository = resolve(SubscriptionDependencyPackageRepositoryInterface::class);

        $oldDowngradedPackageIds = $oldUpgradedPackageIds = [];

        if (null !== $package->upgradedPackages) {
            $oldUpgradedPackageIds = Arr::pluck($package->upgradedPackages->toArray(), 'dependency_package_id');
        }

        if (null !== $package->downgradedPackage) {
            $oldDowngradedPackageIds = [$package->downgradedPackage->dependency_package_id];
        }

        $dependencyRepository->updateMultipleDependencies(
            $context,
            $package->entityId(),
            $oldUpgradedPackageIds,
            $upgradedPackageIds,
            Helper::DEPENDENCY_UPGRADE
        );

        $dependencyRepository->updateMultipleDependencies(
            $context,
            $package->entityId(),
            $oldDowngradedPackageIds,
            $downgradedPackageIds,
            Helper::DEPENDENCY_DOWNGRADE
        );
    }

    public function deletePackage(User $context, int $id): bool
    {
        $package = $this
            ->with(['comparisonData'])
            ->find($id);

        if (policy_check(SubscriptionPackagePolicy::class, 'markAsDeleted', $package)) {
            $success = $package->update(['status' => Helper::STATUS_DELETED]);

            if ($success) {
                $this->handleAfterDeletingPackage($package);
            }

            return $success;
        }

        $package->delete();

        $this->clearCaches();

        return true;
    }

    public function deletePackageLogo(SubscriptionPackage $package): void
    {
        if ($package->image_file_id) {
            app('storage')->rollDown($package->image_file_id);
        }
    }

    public function handleAfterDeletingPackage(SubscriptionPackage $package): void
    {
        $this->deletePackageLogo($package);

        if (null !== $package->recurring_period) {
            $activeSubscriptions = $package->activeSubscriptions;

            if (null !== $activeSubscriptions) {
                foreach ($activeSubscriptions as $activeSubscription) {
                    DeletePackage::dispatch($activeSubscription);
                }
            }
        }

        $this->clearCaches();
    }

    public function clearCaches(): void
    {
        $cacheIds = [
            Helper::ACTIVE_PACKAGE_CACHE_ID, Helper::ALL_PACKAGE_CACHE_ID, Helper::ACTIVE_RECURRING_PACKAGE_CACHE_ID,
        ];

        Cache::deleteMultiple($cacheIds);

        resolve(SubscriptionComparisonRepositoryInterface::class)->clearCaches();
    }

    public function getActivePackages(): Collection
    {
        return Cache::remember(
            Helper::ACTIVE_PACKAGE_CACHE_ID,
            Helper::DEFAULT_CACHE_TTL,
            function () {
                return $this->getModel()->newModelQuery()
                    ->where('status', '=', Helper::STATUS_ACTIVE)
                    ->orderBy('ordering')
                    ->get();
            }
        );
    }

    public function hasDisableFields(int $id, bool $includePastSubscription = false): bool
    {
        return $this->hasPaidSubscriptions($id);
    }

    public function filterPackagesByCurrencyId(User $context, Collection $packages): Collection
    {
        if (!$packages->count()) {
            return $packages;
        }

        $currencyId = app('currency')->getUserCurrencyId($context);

        return $packages->filter(function ($package) use ($currencyId) {
            $prices = $package->getPrices();

            if (!is_array($prices)) {
                return false;
            }

            return Arr::has($prices, $currencyId);
        });
    }

    public function viewPackages(User $context, array $attributes = []): Collection
    {
        $view = Arr::get($attributes, 'view', Helper::VIEW_FILTER);

        if ($view == Helper::VIEW_FILTER && $context->isGuest()) {
            return $this->filterPackagesByCurrencyId($context, $this->viewPackagesForRegistration());
        }

        if ($view == Browse::VIEW_SEARCH) {
            return $this->searchPackages($context, $attributes);
        }

        $isAdminCP = $view == Helper::VIEW_ADMINCP;

        switch ($isAdminCP) {
            case true:
                $packages = Cache::remember(Helper::ALL_PACKAGE_CACHE_ID, Helper::DEFAULT_CACHE_TTL, function () {
                    $collection = $this->getModel()->newModelQuery()
                        ->with(['description'])
                        ->where('status', '<>', Helper::STATUS_DELETED)
                        ->orderByRaw(DB::raw('CASE status WHEN \'' . Helper::STATUS_ACTIVE . '\' THEN 1 ELSE 2 END ASC')->getValue(DB::getQueryGrammar()))
                        ->orderBy('ordering')
                        ->get();

                    return $this->resolvePopularPackage($collection);
                });
                break;
            default:
                $packages = $this->getActivePackages();
                break;
        }

        if ($packages->count() && !$isAdminCP) {
            $packages = $this->filterPackages($context, $packages, true);
            $packages = $this->filterPackagesByCurrencyId($context, $packages);

            if (!$context->hasSuperAdminRole() && $packages->count()) {
                $packages = $packages->filter(function ($value) use ($context) {
                    return null !== $value->isPurchased || policy_check(
                            SubscriptionPackagePolicy::class,
                            'purchase',
                            $context,
                            $value
                        );
                });
            }
        }

        return $packages;
    }

    public function searchPackages(User $context, array $attributes): Collection
    {
        $isAdminCP = Arr::get($attributes, 'is_admincp', false);

        $search = Arr::get($attributes, 'q', '');

        $status = Arr::get($attributes, 'status');

        $statisticOption = Arr::get($attributes, 'payment_statistic');

        $statisticFromDate = Arr::get($attributes, 'payment_statistic_from');

        $statisticToDate = Arr::get($attributes, 'payment_statistic_to');

        $type = Arr::get($attributes, 'type');

        $query = $this->getModel()->newModelQuery();

        $query->leftJoin('subscription_packages_text', function (JoinClause $joinClause) {
            $joinClause->on('subscription_packages_text.id', '=', 'subscription_packages.id');
        });

        if (null === $status) {
            $statusWhere = [Helper::STATUS_ACTIVE];

            if ($isAdminCP) {
                $statusWhere = Arr::prepend($statusWhere, Helper::STATUS_DEACTIVE);
            }
        } else {
            $statusWhere = [$status];
        }

        $query->whereIn('subscription_packages.status', $statusWhere);

        if (null !== $search && '' !== $search) {
            $query->addScope(new SearchScope($search, ['subscription_packages.title']));
        }

        if (null !== $type) {
            $query->whereNull('subscription_packages.recurring_period', 'and', $type == Helper::PACKAGE_TYPE_RECURRING);
        }

        if (
            $statisticOption == Helper::STATISTICS_CUSTOM
            && (null !== $statisticFromDate || null !== $statisticToDate)
        ) {
            $format = $this->getModel()->getDateFormat();

            if (null !== $statisticFromDate) {
                $statisticFromDate = Carbon::parse($statisticFromDate)->format($format);
                $query->where('subscription_packages.created_at', '>=', $statisticFromDate);
            }

            if (null !== $statisticToDate) {
                $statisticToDate = Carbon::parse($statisticToDate)->format($format);
                $query->where('subscription_packages.created_at', '<=', $statisticToDate);
            }
        }

        $packages = $query
            ->with(['description'])
            ->orderBy('subscription_packages.ordering')
            ->get([
                'subscription_packages.*', 'subscription_packages_text.text', 'subscription_packages_text.text_parsed',
            ]);

        if ($packages->count()) {
            switch ($isAdminCP) {
                case true:
                    $packages = $this->resolvePopularPackage($packages);
                    break;
                default:
                    $packages = $this->filterPackages($context, $packages);

                    $packages = $this->filterPackagesByCurrencyId($context, $packages);

                    if ($packages->count()) {
                        $packages = $packages->filter(function ($value) use ($context) {
                            return $value->isPurchased || policy_check(
                                    SubscriptionPackagePolicy::class,
                                    'purchase',
                                    $context,
                                    $value
                                );
                        });
                    }
            }
        }

        return $packages;
    }

    protected function handleStatisticsByDate(Collection $packages, string $fromDate, string $toDate): Collection
    {
        $packageIds = $packages->pluck('id')->toArray();

        $statusStatistics = [
            Helper::getCompletedPaymentStatus() => 'total_success',
            Helper::getExpiredPaymentStatus()   => 'total_expired',
            Helper::getCanceledPaymentStatus()  => 'total_canceled',
        ];

        foreach ($statusStatistics as $status => $column) {
            $statistic = resolve(SubscriptionInvoiceRepositoryInterface::class)->getStatisticsByPaymentStatus(
                $packageIds,
                $status,
                $fromDate,
                $toDate
            );

            if (is_array($statistic)) {
                $packages = $packages->map(function ($package) use ($statistic, $column) {
                    $value = Arr::get($statistic, $package->entityId());

                    $package->{$column} = $value ?: 0;

                    return $package;
                });
            }
        }

        return $packages;
    }

    public function filterPackages(
        User       $context,
        Collection $packages,
        bool       $includeUserActivePackage = false
    ): Collection {
        $activeSubscription = resolve(SubscriptionInvoiceRepositoryInterface::class)->getUserActiveSubscription($context);

        if (null === $activeSubscription) {
            return $this->filterPackagesByUserRole($context, $packages);
        }

        if (null === $activeSubscription->package || null === $activeSubscription->package->upgradedPackages || !$activeSubscription->package->upgradedPackages->count()) {
            return $this->filterPackagesByUserRole($context, $packages, $activeSubscription->package);
        }

        $upgradedPackages = $activeSubscription->package->upgradedPackages->toArray();

        $upgradedPackageIds = Arr::pluck($upgradedPackages, 'dependency_package_id');

        if ($includeUserActivePackage) {
            $upgradedPackageIds[] = $activeSubscription->package->entityId();
        }

        return $this->filterPackagesByDependencies($packages, $upgradedPackageIds);
    }

    protected function filterPackagesByUserRole(
        User                 $context,
        Collection           $packages,
        ?SubscriptionPackage $userActivePackage = null
    ): Collection {
        $userRole = resolve(RoleRepositoryInterface::class)->roleOf($context);

        $userRoleId = $userRole->entityId();

        foreach ($packages as $key => $package) {
            if (null !== $userActivePackage && $userActivePackage->entityId() == $package->entityId()) {
                continue;
            }

            if (null === $package->visible_roles) {
                continue;
            }

            $visibleRoles = json_decode($package->visible_roles, true);

            if (!is_array($visibleRoles) || !in_array($userRoleId, $visibleRoles)) {
                if ($context->hasSuperAdminRole()) {
                    continue;
                }

                $packages->forget($key);
            }
        }

        return $packages;
    }

    protected function filterPackagesByDependencies(Collection $packages, array $dependencyPackageIds = []): Collection
    {
        if (!count($dependencyPackageIds)) {
            return $packages;
        }

        foreach ($packages as $key => $package) {
            if (!in_array($package->entityId(), $dependencyPackageIds)) {
                $packages->forget($key);
            }
        }

        return $packages;
    }

    public function viewPackage(User $context, int $id, array $attributes = []): ?SubscriptionPackage
    {
        return $this->with(['description'])
            ->find($id);
    }

    public function markAsPopular(User $context, int $id, bool $isPopular): bool
    {
        $package = $this->find($id);

        if ($isPopular) {
            $model = $this->getModel();

            $model->timestamps = false;

            $model->newModelQuery()
                ->where('id', '<>', $id)
                ->update(['is_popular' => false]);
        }

        $this->clearCaches();

        return $package->update(['is_popular' => $isPopular]);
    }

    public function activePackage(User $context, int $id, bool $isActive): bool
    {
        $package = $this->find($id);

        $status = $isActive ? Helper::STATUS_ACTIVE : Helper::STATUS_DEACTIVE;

        $package->fill([
            'status' => $status,
        ]);

        $this->clearCaches();

        return $package->save();
    }

    public function updateTotalItem(int $id, string $status, string $action = Helper::ACTION_PLUS, int $total = 1): void
    {
        $package = $this->find($id);

        switch ($status) {
            case Helper::getCompletedPaymentStatus():
                $column = 'total_success';
                break;
            case Helper::getCanceledPaymentStatus():
                $column = 'total_canceled';
                break;
            case Helper::getExpiredPaymentStatus():
                $column = 'total_expired';
                break;
            case Helper::getPendingPaymentStatus():
                $column = 'total_pending';
                break;
            default:
                $column = null;
                break;
        }

        if (null !== $column) {
            switch ($action) {
                case Helper::ACTION_PLUS:
                    $package->incrementAmount($column, $total);
                    break;
                case Helper::ACTION_SUBTRACT:
                    $package->decrementAmount($column, $total);
                    break;
            }
        }
    }

    public function hasPaidSubscriptions(int $id, bool $includePastSubscription = false): bool
    {
        $package = $this->find($id);

        if (null === $package) {
            return false;
        }

        $granted = $package->total_success || $package->total_pending;

        if ($includePastSubscription && !$granted) {
            $granted = $package->total_canceled || $package->total_expired;
        }

        return $granted;
    }

    public function getRecurringPackages(): Collection
    {
        return Cache::remember(Helper::ACTIVE_RECURRING_PACKAGE_CACHE_ID, Helper::DEFAULT_CACHE_TTL, function () {
            return $this->getModel()->newModelQuery()
                ->whereNotNull('recurring_period')
                ->get();
        });
    }

    protected function filterPackagesByRoleId(Collection $packages, int $roleId): Collection
    {
        if ($packages->count()) {
            foreach ($packages as $key => $package) {
                if (null === $package->visible_roles) {
                    continue;
                }

                $visibleRoles = json_decode($package->visible_roles, true);

                if (!is_array($visibleRoles) || !in_array($roleId, $visibleRoles)) {
                    $packages->forget($key);
                }
            }
        }

        return $packages;
    }

    public function viewPackagesForRegistration(bool $hasAppendInformation = false): Collection
    {
        $packages = $this->getActivePackages();

        $guestRole = resolve(RoleRepositoryInterface::class)->roleOf(null);

        $packages = $this->filterPackagesByRoleId($packages, $guestRole->entityId());

        if (!$packages->count()) {
            return $packages;
        }

        $packages = $packages->filter(function ($package) {
            if (!$package->is_on_registration) {
                return false;
            }

            return true;
        });

        if (!$hasAppendInformation) {
            return $packages;
        }

        $guestUser = UserFacade::getGuestUser();

        $packages = $packages->map(function ($package) use ($guestUser) {
            return $this->appendInformationForRegistration($guestUser, $package);
        });

        return $packages;
    }

    protected function appendInformationForRegistration(
        User                $context,
        SubscriptionPackage $package
    ): SubscriptionPackage {
        if (Facade::isFreePackageForUser($context, $package)) {
            $package->title .= ' - ' . __p('subscription::phrase.free');

            return $package;
        }

        $defaultCurrency = app('currency')->getDefaultCurrencyId();

        if (Facade::isFirstFreeAndRecurringForUser($context, $package)) {
            $prices = $package->getRecurringPrices();

            $price = Arr::get($prices, $defaultCurrency);

            if (null !== $price) {
                $priceFormat    = app('currency')->getPriceFormatByCurrencyId($defaultCurrency, $price);
                $period         = Helper::getPeriodLabel($package->recurring_period);
                $package->title .= ' - ' . strip_tags(__p(
                        'subscription::phrase.recurring_price_info_with_free',
                        ['price' => $priceFormat, 'period' => strtolower($period)]
                    ));
            }

            return $package;
        }

        $prices = $package->getPrices();

        $recurringPrices = $package->getRecurringPrices();

        if (is_array($prices)) {
            $price = Arr::get($prices, $defaultCurrency);

            if (null !== $price) {
                $package->title .= ' - ' . app('currency')->getPriceFormatByCurrencyId($defaultCurrency, $price);
            }
        }

        if ($package->is_recurring && is_array($recurringPrices)) {
            $price = Arr::get($recurringPrices, $defaultCurrency);

            if (null !== $price) {
                $priceFormat    = app('currency')->getPriceFormatByCurrencyId($defaultCurrency, $price);
                $period         = Helper::getPeriodLabel($package->recurring_period);
                $package->title .= ' (' . strip_tags(__p(
                        'subscription::phrase.recurring_price_info',
                        ['price' => $priceFormat, 'period' => $period]
                    )) . ')';
            }
        }

        return $package;
    }

    public function updateRoleId(int $oldRoleId, int $alternativeRoleId): bool
    {
        $total = $this->getModel()->newQuery()
            ->where(['upgraded_role_id' => $oldRoleId])
            ->update(['upgraded_role_id' => $alternativeRoleId]);

        return $total > 0;
    }

    public function updateDowngradedUserRole(int $oldRoleId): void
    {
        $downgradedUserRole = (int) Settings::get('subscription.default_downgraded_user_role');
        if ($oldRoleId != $downgradedUserRole) {
            return;
        }

        Settings::save(['subscription.default_downgraded_user_role' => UserRole::NORMAL_USER]);
    }

    protected function resolvePopularPackage(Collection $packages): Collection
    {
        if (!$packages->count()) {
            return $packages;
        }

        return $packages->map(function ($model) {
            if (!$model->is_popular) {
                return $model;
            }

            $model->title = Facade::resolvePopularTitle($model->title);

            return $model;
        });
    }

    /**
     * @param SubscriptionPackage $package
     *
     * @return array
     */
    public function getFeaturesPackage(SubscriptionPackage $package): array
    {
        return Cache::rememberForever(Helper::getFeaturePackageCacheId($package->entityId()), function () use ($package) {
            $comparisonData    = $package->comparisonData;
            $convertedPackages = [];

            if (null !== $comparisonData) {
                $comparisonData = $comparisonData->toArray();
            }

            foreach ($comparisonData as $data) {
                $type = Arr::get($data, 'type');

                if ($type == Helper::COMPARISON_TYPE_NO) {
                    continue;
                }

                $value = match ($type) {
                    Helper::COMPARISON_TYPE_YES => __p('core::phrase.yes'),
                    default                     => Arr::get($data, 'value'),
                };

                $feature = [
                    'title' => Helper::handleTitleForView(Arr::get($data, 'comparisons_title')),
                    'type'  => $type,
                    'value' => $value,
                ];

                Arr::set($convertedPackages, Arr::get($data, 'comparison_id'), $feature);
            }

            return $convertedPackages;
        });
    }
}
