<?php

namespace MetaFox\Core\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Driver.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface DriverRepositoryInterface
{
    /**
     * Get driver handler class name.
     *
     * @param string $type
     * @param string $name
     * @param string $resolution
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function getDriver(string $type, string $name, string $resolution): string;

    /**
     * Import drivers from "resources/drivers.php".
     *
     * @param string $package
     * @param array  $drivers
     */
    public function setupDrivers(string $package, array $drivers): void;

    /**
     * @param  string      $type
     * @param  string|null $category
     * @param  string|null $resolution
     * @return Collection
     */
    public function getDrivers(string $type, ?string $category, ?string $resolution): Collection;

    /**
     * @param string $packageName
     *
     * @return string
     */
    public function exportDriverToFilesystem(string $packageName): string;

    /**
     * @param bool $admin
     *
     * @return array<string,string>
     */
    public function getJsonResources(bool $admin): array;

    /**
     * @return array
     */
    public function loadPolicyRules(): array;

    /**
     * @return array
     */
    public function loadPolicies(): array;

    /**
     * @return array
     */
    public function loadEntities(): array;

    /**
     * @return Builder
     */
    public function getLoadEntitiesEloquentBuilder(): Builder;

    /**
     * Load all drivers by type, admin, active, and version.
     *
     * @param  string          $type
     * @param  string|null     $resolution
     * @param  bool|null       $active
     * @param  string|null     $version
     * @param  bool|null       $preload
     * @param  string|null     $packageId
     * @return array<string[]> Get [[$name, $driver, $version], ... ] array.
     */
    public function loadDrivers(
        string $type,
        ?string $resolution = null,
        ?bool $active = true,
        ?string $version = null,
        ?bool $preload = null,
        ?string $packageId = null
    ): array;

    public function getEntityPackageAlias(): array;

    /**
     * @param  string      $name
     * @return string|null
     */
    public function getPackageIdByEntityType(string $name): ?string;

    /**
     * @return array
     */
    public function getEntityPackageIds(): array;

    /**
     * @param  string       $type
     * @param  Closure|null $filter
     * @param  Closure|null $map
     * @return array
     */
    public function loadDriverWithCallback(string $type, ?Closure $filter, ?Closure $map): array;

    /**
     * @param  string   $type
     * @return string[]
     */
    public function getNamesHasHandlerClass(string $type): array;

    /**
     * Get driver handler class name.
     *
     * @param string      $type
     * @param string      $name
     * @param string|null $resolution
     *
     * @return array<string>            result includes $name, $drivers, $version, $package_id
     * @throws InvalidArgumentException
     */
    public function loadDriver(string $type, string $name, ?string $resolution = null): array;

    /**
     * @return void
     */
    public function bootingKernelConfigs(): void;

    /**
     * @return array<string, string>
     */
    public function loadEntityModuleMap(): array;
}
