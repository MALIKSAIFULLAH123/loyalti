<?php

namespace MetaFox\Subscription\Contracts;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Support\Helper;

interface SubscriptionPackageContract
{
    /**
     * @param  ?int $id
     * @return bool
     */
    public function hasDisableFields(?int $id): bool;

    /**
     * @param  Model $package
     * @return void
     */
    public function handleAfterDeletingPackage(Model $package): void;

    /**
     * @param  Model $package
     * @param  bool  $includePastSubscription
     * @return bool
     */
    public function canMarkAsDeleted(Model $package, bool $includePastSubscription = false): bool;

    /**
     * @param  User            $context
     * @param  array           $attributes
     * @return Collection|null
     */
    public function getPackages(User $context, array $attributes = []): ?Collection;

    /**
     * @param  bool   $aborted
     * @param  string $view
     * @return bool
     */
    public function hasPackages(bool $aborted = false, string $view = Helper::VIEW_FILTER): bool;

    /**
     * @param  int   $id
     * @return array
     */
    public function getPackageRenewMethodOptions(int $id): array;

    /**
     * @return array
     */
    public function getRoleOptionsOnSuccess(bool $byKey = false): array;

    /**
     * @param  User  $context
     * @param  Model $package
     * @return bool
     */
    public function isFreePackageForUser(User $context, Model $package): bool;

    /**
     * @return bool
     */
    public function allowUsingPackages(): bool;

    /**
     * @param  bool       $hasAppendInformation
     * @return Collection
     */
    public function getPackagesForRegistration(bool $hasAppendInformation = false): Collection;

    /**
     * @param  User  $context
     * @param  Model $package
     * @return bool
     */
    public function isFirstFreeAndRecurringForUser(User $context, Model $package): bool;

    /**
     * @param  string $title
     * @return string
     */
    public function resolvePopularTitle(string $title): string;

    /**
     * @param Model $package
     * @return array
     */
    public function getFeaturesPackage(Model $package): array;

    /**
     * @param Model  $package
     * @param string $method
     * @return bool
     */
    public function hasOnlySpecificMethod(Model $package, string $method): bool;
}
