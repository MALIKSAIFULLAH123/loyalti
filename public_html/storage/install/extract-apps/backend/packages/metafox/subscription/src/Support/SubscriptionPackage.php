<?php

namespace MetaFox\Subscription\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Subscription\Contracts\SubscriptionPackageContract;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\User\Support\Facades\User as UserFacade;

class SubscriptionPackage implements SubscriptionPackageContract
{
    /**
     * @var SubscriptionPackageRepositoryInterface
     */
    protected $repository;

    public function __construct(SubscriptionPackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function hasDisableFields(?int $id): bool
    {
        if (!$id) {
            return false;
        }

        return $this->repository->hasDisableFields($id);
    }

    public function handleAfterDeletingPackage(Model $package): void
    {
        $this->repository->handleAfterDeletingPackage($package);
    }

    public function canMarkAsDeleted(Model $package, bool $includePastSubscription = false): bool
    {
        return $this->repository->hasPaidSubscriptions($package->entityId(), $includePastSubscription);
    }

    public function getPackages(User $context, array $attributes = []): ?Collection
    {
        return $this->repository->viewPackages($context, $attributes);
    }

    public function hasPackages(bool $aborted = false, string $view = Helper::VIEW_FILTER): bool
    {
        $context = match (Auth::guest()) {
            true  => UserFacade::getGuestUser(),
            false => user(),
        };

        $params = [
            'view' => $view,
        ];

        $packages = $this->getPackages($context, $params);

        $granted = null !== $packages && $packages->count();

        if (!$granted && $aborted) {
            abort(403, __p('subscription::admin.no_packages_found_for_comparison_feature'));
        }

        return $granted;
    }

    public function getPackageRenewMethodOptions(int $id): array
    {
        $package = $this->repository->find($id);

        $options = [];

        if (null !== $package->allowed_renew_type) {
            $packageMethods = json_decode($package->allowed_renew_type, true);

            $defaultMethods = Helper::getAllowedRenewMethod();

            foreach ($defaultMethods as $defaultMethod) {
                if (in_array($defaultMethod['value'], $packageMethods)) {
                    $options[] = $defaultMethod;
                }
            }
        }

        return $options;
    }

    public function getRoleOptionsOnSuccess(bool $byKey = false): array
    {
        $options = $this->repository->getRoleOptionsForSuccess();

        if ($byKey) {
            $options = array_combine(Arr::pluck($options, 'value'), Arr::pluck($options, 'label'));
        }

        return $options;
    }

    public function isFreePackageForUser(User $context, Model $package): bool
    {
        $isFreeInitialPrice = $package->is_free;

        $userCurrencyId = app('currency')->getUserCurrencyId($context);

        if (!$isFreeInitialPrice) {
            $prices = $package->getPrices();

            if (null === $prices) {
                return false;
            }

            if (!Arr::has($prices, $userCurrencyId)) {
                return false;
            }

            $userPrice = (float) Arr::get($prices, $userCurrencyId);

            if ($userPrice != 0) {
                return false;
            }
        }

        if (!$package->is_recurring) {
            return true;
        }

        $recurringPrices = $package->getRecurringPrices();

        if (null === $recurringPrices || !Arr::get($recurringPrices, $userCurrencyId)) {
            return false;
        }

        return (float) Arr::get($recurringPrices, $userCurrencyId) == 0;
    }

    public function allowUsingPackages(): bool
    {
        return Settings::get('subscription.enable_subscription_packages', true);
    }

    public function getPackagesForRegistration(bool $hasAppendInformation = false): Collection
    {
        return $this->repository->viewPackagesForRegistration($hasAppendInformation);
    }

    public function isFirstFreeAndRecurringForUser(User $context, Model $package): bool
    {
        $prices = $package->getPrices();

        if (null === $prices) {
            return false;
        }

        $userCurrencyId = app('currency')->getUserCurrencyId($context);

        if (!Arr::has($prices, $userCurrencyId)) {
            return false;
        }

        $price = (float) Arr::get($prices, $userCurrencyId);

        if ($price != 0 || !$package->is_recurring) {
            return false;
        }

        $recurringPrices = $package->getRecurringPrices();

        if (null === $recurringPrices || !Arr::get($recurringPrices, $userCurrencyId)) {
            return false;
        }

        return (float) Arr::get($recurringPrices, $userCurrencyId) > 0;
    }

    public function resolvePopularTitle(string $title): string
    {
        return $title . ' (' . __p('subscription::admin.most_popular') . ')';
    }

    public function getFeaturesPackage(Model $package): array
    {
        return $this->repository->getFeaturesPackage($package);
    }

    public function hasOnlySpecificMethod(Model $package, string $method): bool
    {
        $methods = $package->renew_methods;

        if (null === $methods) {
            return false;
        }

        if (count($methods) > 1) {
            return false;
        }

        $value = array_shift($methods);

        if ($value !== $method) {
            return false;
        }

        return true;
    }
}
