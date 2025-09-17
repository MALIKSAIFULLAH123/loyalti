<?php

namespace MetaFox\Featured\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Featured\Models\Package;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Package
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface PackageRepositoryInterface
{
    /**
     * @param array $data
     * @return Package
     */
    public function createPackage(array $data): Package;

    /**
     * @param Package $package
     * @param array   $data
     * @return Package
     */
    public function updatePackage(Package $package, array $data): Package;

    /**
     * @param Package $package
     * @return bool
     */
    public function deletePackage(Package $package): bool;

    /**
     * @param array $attributes
     * @return Paginator
     */
    public function viewAdmincpPackages(array $attributes = []): Paginator;

    /**
     * @param User   $user
     * @param string $entityType
     * @return array
     */
    public function getPackageOptionsForEntityType(User $user, string $entityType): array;

    /**
     * @param int $packageId
     * @return Package|null
     */
    public function getPackageById(int $packageId): ?Package;

    /**
     * @param int $packageId
     * @return void
     */
    public function increasePackageTotalActive(int $packageId): void;

    /**
     * @param int $packageId
     * @return void
     */
    public function increasePackageTotalEnd(int $packageId): void;

    /**
     * @param int $packageId
     * @return void
     */
    public function increasePackageTotalCancelled(int $packageId): void;

    /**
     * @param int $packageId
     * @return void
     */
    public function decreasePackageTotalActive(int $packageId): void;

    /**
     * @param int $packageId
     * @return void
     */
    public function decreasePackageTotalEnd(int $packageId): void;

    /**
     * @param int $packageId
     * @return void
     */
    public function decreasePackageTotalCancelled(int $packageId): void;

    /**
     * @return array
     */
    public function getPackageSearchOptions(): array;

    /**
     * @param User  $context
     * @param array $attributes
     * @return Collection
     */
    public function viewPackagesForSearch(User $context, array $attributes = []): Collection;
}
