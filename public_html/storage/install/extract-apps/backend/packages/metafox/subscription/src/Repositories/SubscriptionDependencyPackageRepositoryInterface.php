<?php

namespace MetaFox\Subscription\Repositories;

use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionDependencyPackage;

interface SubscriptionDependencyPackageRepositoryInterface
{
    /**
     * @param  User                          $context
     * @param  array                         $attributes
     * @return SubscriptionDependencyPackage
     */
    public function createDependency(User $context, array $attributes): ?SubscriptionDependencyPackage;

    /**
     * @param  User  $context
     * @param  array $attributes
     * @return bool
     */
    public function deleteDependency(User $context, array $attributes): bool;

    /**
     * @param  User   $context
     * @param  int    $currentId
     * @param  array  $dependencyIds
     * @param  string $dependencyType
     * @return void
     */
    public function createMultipleDependencies(User $context, int $currentId, array $dependencyIds, string $dependencyType): void;

    /**
     * @param  User   $context
     * @param  int    $currentId
     * @param  array  $currentDependencyIds
     * @param  array  $newDependencyIds
     * @param  string $dependencyType
     * @return void
     */
    public function updateMultipleDependencies(User $context, int $currentId, array $currentDependencyIds, array $newDependencyIds, string $dependencyType): void;

    /**
     * @param  User   $context
     * @param  int    $currentId
     * @param  array  $dependencyIds
     * @param  string $dependencyType
     * @return void
     */
    public function deleteMultipleDependencies(User $context, int $currentId, array $dependencyIds, string $dependencyType): void;
}
