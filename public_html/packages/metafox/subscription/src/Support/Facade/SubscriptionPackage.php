<?php

namespace MetaFox\Subscription\Support\Facade;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Contracts\SubscriptionPackageContract;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Support\Helper;

/**
 * @method static Collection      getPackagesForRegistration(bool $hasAppendInformation)
 * @method static string          resolvePopularTitle(string $title)
 * @method static bool            hasDisableFields(int $id)
 * @method static void            handleAfterDeletingPackage(Model $package)
 * @method static bool            canMarkAsDeleted(Model $package, bool $includePastSubscription = false)
 * @method static Collection|null getPackages(User $context, array $attributes = [])
 * @method static bool            hasPackages(bool $aborted = false, string $view = Helper::VIEW_FILTER)
 * @method static array           getPackageRenewMethodOptions(int $id)
 * @method static array           getRoleOptionsOnSuccess(bool $byKey = false)
 * @method static array           getFeaturesPackage(Model $package)
 * @method static bool            isFreePackageForUser(User $context, Model $package)
 * @method static bool            allowUsingPackages()
 * @method static bool            isFirstFreeAndRecurringForUser(User $context, Model $package)
 * @method static bool            hasOnlySpecificMethod(Model $package, string $method)
 */
class SubscriptionPackage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SubscriptionPackageContract::class;
    }
}
