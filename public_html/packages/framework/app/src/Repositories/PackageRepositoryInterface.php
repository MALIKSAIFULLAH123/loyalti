<?php

namespace MetaFox\App\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\App\Models\Package;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use RuntimeException;
use ZipArchive;
use Illuminate\Support\Collection;

/**
 * Interface PackageRepositoryInterface.
 * @method Package getModel()
 * @method Package find($id, $columns = ['*'])
 * @mixin BaseRepository
 */
interface PackageRepositoryInterface
{
    /**
     * @return array<string,string>
     */
    public function getPackageOptions(bool $alias = true): array;

    /**
     * @return array
     */
    public function getPackageHasPermissionOptions(): array;

    /**
     * @return array<string,string>
     */
    public function getPackageIdOptions(): array;

    /**
     * @return array<string,string>
     */
    public function getResourceOptions(): array;

    /**
     * @param User  $context
     * @param array $params
     *
     * @return Builder
     * @throws AuthorizationException
     */
    public function getAllPackages(User $context, array $params): Builder;

    /**
     * @param string $name
     *
     * @return Package
     */
    public function getPackageByName(string $name): Package;

    /**
     * @param string $alias
     *
     * @return Package
     */
    public function getPackageByAlias(string $alias): Package;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $params
     *
     * @return Package
     * @throws AuthorizationException
     */
    public function updatePackage(User $context, int $id, array $params): Package;

    /**
     * Setup Module.
     * @param  string           $packageName
     * @return Package
     * @throws RuntimeException
     */
    public function setupPackage(string $packageName): Package;

    /**
     * @param array<string, mixed> $composer
     *
     * @return ?Package
     */
    public function syncComposerInfo(array $composer): ?Package;

    /**
     * Get ativive module string array
     * result: ['core', 'blog', ...].
     * @return string[]
     */
    public function getActivePackageAliases(): array;

    /**
     * Get build settings for bundling frontend site.
     */
    public function getBuildSettings(?string $reason = null): array;

    /**
     * @param  string $name
     * @param  string $status
     * @return void
     */
    public function setInstallationStatus(string $name, string $status): void;

    /**
     * @param  string       $name
     * @return Package|null
     */
    public function findByName(string $name): ?Package;

    /**
     * @return array
     */
    public function getInternalAdminUrls(): array;

    /**
     * @param  \ArrayObject $data
     * @param  ZipArchive   $zip
     * @return void
     */
    public function attachBuildArchive(\ArrayObject $data, ZipArchive $zip, ?string $reason = null): void;

    /**
     * @param  array<string>       $names
     * @return Collection<Package>
     */
    public function getPackageByNames(array $names): Collection;

    /**
     * @param  string $name
     * @return bool
     */
    public function isAppActive(string $name): bool;

    /**
     * @return Builder
     */
    public function getEloquentBuilder(): Builder;

    /**
     * This method does not care where a package is active or not.
     * It just want to get all installed packages.
     * @return array<string>
     */
    public function getInstalledPackageAliases(): array;
}
