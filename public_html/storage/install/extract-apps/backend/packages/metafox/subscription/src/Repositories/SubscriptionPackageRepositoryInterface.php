<?php

namespace MetaFox\Subscription\Repositories;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SubscriptionPackage.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SubscriptionPackageRepositoryInterface
{
    /**
     * @param  array $disallowedRoles
     * @return array
     */
    public function getRoleOptions(array $disallowedRoles = []): array;

    /**
     * @return array
     */
    public function getRoleOptionsForSuccess(): array;

    /**
     * @return array
     */
    public function getRoleOptionsForVisibility(): array;

    /**
     * @return array
     */
    public function getRoleOptionsForDowngrade(): array;

    /**
     * @param  User                $context
     * @param  array               $attributes
     * @return SubscriptionPackage
     */
    public function createPackage(User $context, array $attributes): SubscriptionPackage;

    /**
     * @param  User                $context
     * @param  int                 $id
     * @param  array               $attributes
     * @return SubscriptionPackage
     */
    public function updatePackage(User $context, int $id, array $attributes): SubscriptionPackage;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deletePackage(User $context, int $id): bool;

    /**
     * @param  int  $id
     * @param  bool $includePastSubscription
     * @return bool
     */
    public function hasDisableFields(int $id, bool $includePastSubscription = false): bool;

    /**
     * @param  User       $context
     * @param  Collection $packages
     * @return Collection
     */
    public function filterPackagesByCurrencyId(User $context, Collection $packages): Collection;

    /**
     * @param  SubscriptionPackage $package
     * @return void
     */
    public function deletePackageLogo(SubscriptionPackage $package): void;

    /**
     * @param  SubscriptionPackage $package
     * @return void
     */
    public function handleAfterDeletingPackage(SubscriptionPackage $package): void;

    /**
     * @return void
     */
    public function clearCaches(): void;

    /**
     * @return Collection|null
     */
    public function getActivePackages(): Collection;

    /**
     * @param  User            $context
     * @param  array           $attributes
     * @return Collection|null
     */
    public function viewPackages(User $context, array $attributes = []): Collection;

    /**
     * @param  User            $context
     * @param  array           $attributes
     * @return Collection|null
     */
    public function searchPackages(User $context, array $attributes): Collection;

    /**
     * @param  User            $context
     * @param  Collection      $packages
     * @return Collection|null
     */
    public function filterPackages(User $context, Collection $packages): Collection;

    /**
     * @param  User                     $context
     * @param  int                      $id
     * @param  array                    $attributes
     * @return SubscriptionPackage|null
     */
    public function viewPackage(User $context, int $id, array $attributes = []): ?SubscriptionPackage;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isPopular
     * @return bool
     */
    public function markAsPopular(User $context, int $id, bool $isPopular): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isActive
     * @return bool
     */
    public function activePackage(User $context, int $id, bool $isActive): bool;

    /**
     * @param  int    $id
     * @param  string $status
     * @param  string $action
     * @param  int    $total
     * @return void
     */
    public function updateTotalItem(int $id, string $status, string $action = Helper::ACTION_PLUS, int $total = 1): void;

    /**
     * @param  int  $id
     * @param  bool $includePastSubscription
     * @return bool
     */
    public function hasPaidSubscriptions(int $id, bool $includePastSubscription = false): bool;

    /**
     * @return Collection
     */
    public function getRecurringPackages(): Collection;

    /**
     * @return Collection
     */
    public function viewPackagesForRegistration(bool $hasAppendInformation = false): Collection;

    /**
     * @param  int  $oldRoleId
     * @param  int  $alternativeRoleId
     * @return bool
     */
    public function updateRoleId(int $oldRoleId, int $alternativeRoleId): bool;

    /**
     * @param  int  $oldRoleId
     * @return void
     */
    public function updateDowngradedUserRole(int $oldRoleId): void;

    /**
     * @param  SubscriptionPackage $package
     * @return array
     */
    public function getFeaturesPackage(SubscriptionPackage $package): array;
}
