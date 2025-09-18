<?php

namespace MetaFox\Subscription\Repositories\Eloquent;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Subscription\Models\SubscriptionDependencyPackage;
use MetaFox\Subscription\Repositories\SubscriptionDependencyPackageRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SubscriptionDependencyPackageRepository.
 */
class SubscriptionDependencyPackageRepository extends AbstractRepository implements SubscriptionDependencyPackageRepositoryInterface
{
    public function model()
    {
        return SubscriptionDependencyPackage::class;
    }

    public function createDependency(User $context, array $attributes): ?SubscriptionDependencyPackage
    {
        $isExisted = $this->getModel()->newModelQuery()
            ->where($attributes)
            ->exists();

        if ($isExisted) {
            return null;
        }

        $dependency = $this->getModel()->newInstance();

        $dependency->fill($attributes);

        $dependency->save();

        return $dependency;
    }

    public function deleteDependency(User $context, array $attributes): bool
    {
        $query = $this->getModel()->newModelQuery()
            ->where($attributes);

        if (!$query->exists()) {
            return false;
        }

        return $query->delete();
    }

    public function createMultipleDependencies(User $context, int $currentId, array $dependencyIds, string $dependencyType): void
    {
        foreach ($dependencyIds as $dependencyId) {
            $this->createDependency($context, [
                'current_package_id'    => $currentId,
                'dependency_package_id' => $dependencyId,
                'dependency_type'       => $dependencyType,
            ]);
        }
    }

    public function updateMultipleDependencies(User $context, int $currentId, array $currentDependencyIds, array $newDependencyIds, string $dependencyType): void
    {
        $newIds = array_diff($newDependencyIds, $currentDependencyIds);

        $deletedIds = array_diff($currentDependencyIds, $newDependencyIds);

        if ($newIds) {
            $this->createMultipleDependencies($context, $currentId, $newIds, $dependencyType);
        }

        if ($deletedIds) {
            $this->deleteMultipleDependencies($context, $currentId, $deletedIds, $dependencyType);
        }
    }

    public function deleteMultipleDependencies(User $context, int $currentId, array $dependencyIds, string $dependencyType): void
    {
        foreach ($dependencyIds as $dependencyId) {
            $this->deleteDependency($context, [
                'current_package_id'    => $currentId,
                'dependency_package_id' => $dependencyId,
                'dependency_type'       => $dependencyType,
            ]);
        }
    }
}
