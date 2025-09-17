<?php

namespace MetaFox\Core\Repositories\Eloquent;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\Driver;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class DriverRepository.
 */
class DriverRepository extends AbstractRepository implements DriverRepositoryInterface
{
    public function model()
    {
        return Driver::class;
    }

    /**
     * Import drivers from "resources/drivers.php".
     *
     * @param string       $package
     * @param array<array> $drivers
     */
    public function setupDrivers(string $package, array $drivers): void
    {
        if (empty($drivers)) {
            return;
        }

        $fields = $this->getModel()->getFillable();

        $packageId = PackageManager::getName($package);
        $moduleId  = PackageManager::getAlias($package);

        foreach ($drivers as $driver) {
            $driver['package_id'] = $packageId;
            $driver['module_id']  = $moduleId;
            $className            = $driver['driver'] ?? null;

            if ($driver['is_deleted'] ?? false || ($className && !class_exists($className))) {
                Driver::query()->where(Arr::only($driver, ['type', 'name', 'version', 'resolution']))->delete();
                continue;
            }

            Driver::query()->updateOrCreate(
                Arr::only($driver, ['type', 'name', 'version', 'resolution']),
                Arr::only($driver, $fields)
            );
        }
    }

    public function getDriver(string $type, string $name, string $resolution): string
    {
        /** @var Driver $resource */
        $resource = $this->getModel()->newQuery()->where([
            ['type', '=', $type],
            ['name', '=', str_replace('-', '_', $name)],
            ['resolution', '=', $resolution],
        ])->whereIn('package_id', app('core.packages')->getActivePackageIds())->firstOrFail();

        try {
            app('events')->dispatch('core.driver.override_get_driver', [$resource, $type, $name, $resolution]);
        } catch (\Throwable $throwable) {
            Log::error('override get driver error message: ' . $throwable->getMessage());
        }

        return $resource->driver;
    }

    /**
     * @param  string      $type
     * @param  string|null $category
     * @param  string|null $resolution
     * @return Collection
     */
    public function getDrivers(string $type, ?string $category, ?string $resolution): Collection
    {
        $wheres = [
            ['type', '=', $type],
            ['resolution', '=', $resolution],
        ];

        if ($category) {
            $wheres[] = ['category', '=', $category];
        }

        return $this->getModel()->newQuery()->where($wheres)->get();
    }

    public function exportDriverToFilesystem(string $packageName): string
    {
        $drivers = $this->getModel()
            ->newQuery()
            ->where([['package_id', '=', $packageName]])
            ->orderBy('type')
            ->orderBy('name')
            ->get([
                'driver',
                'type',
                'name',
                'version',
                'resolution',
                'alias',
                'is_active',
                'is_preload',
                'title',
                'url',
                'description',
            ])
            ->toArray();

        $drivers = array_map(function (array $values) {
            return array_trim_null($values, [
                'url'         => '',
                'description' => '',
                'alias'       => '',
                'version'     => '*',
                'is_preload'  => 0,
                'is_active'   => 1,
            ]);
        }, array_values($drivers));

        return PackageManager::exportToFilesystem($packageName, 'resources/drivers.php', $drivers);
    }

    public function getJsonResources(bool $admin): array
    {
        $response = [];

        $this->getModel()->newQuery()
            ->where([
                'type'       => Constants::DRIVER_TYPE_JSON_RESOURCE,
                'resolution' => $admin ? 1 : 0,
                'is_active'  => 1,
            ])
            ->get(['name', 'version', 'driver'])
            ->each(function (Driver $model) use (&$response) {
                Arr::set($response, sprintf('%s.%s', $model->name, $model->version), $model->driver);
            });

        return $response;
    }

    public function loadDriverWithCallback(string $type, ?Closure $filter, ?Closure $map): array
    {
        // TODO should join to active package.
        $query = $this->getModel()->newQuery()
            ->where('type', $type)
            ->orderBy('name')
            ->get();

        if ($filter) {
            $query = $query->filter($filter);
        }
        if ($map) {
            $query = $query->map($map);
        }

        return $query->toArray();
    }

    public function getNamesHasHandlerClass(string $type): array
    {
        return $this->loadDriverWithCallback($type, function (Driver $model) {
            return $model->driver && class_exists($model->driver);
        }, function (Driver $model) {
            return $model->name;
        });
    }

    public function loadPolicies(): array
    {
        return $this->getModel()->newQuery()
            ->where('type', Constants::DRIVER_TYPE_POLICY_RESOURCE)
            ->whereIn('package_id', app('core.packages')->getActivePackageIds())
            ->orderBy('name')
            ->get(['name', 'driver'])
            ->pluck('driver', 'name')
            ->toArray();
    }

    public function loadPolicyRules(): array
    {
        return $this->getModel()->newQuery()
            ->where('type', Constants::DRIVER_TYPE_POLICY_RULE)
            ->whereIn('package_id', app('core.packages')->getActivePackageIds())
            ->orderBy('name')
            ->get(['name', 'driver'])
            ->pluck('driver', 'name')
            ->toArray();
    }

    public function loadEntities(): array
    {
        return localCacheStore()->rememberForever(__METHOD__, function () {
            return $this->getLoadEntitiesEloquentBuilder()
                ->orderBy('name')
                ->get(['name', 'driver'])
                ->pluck('driver', 'name')
                ->toArray();
        });
    }

    public function getLoadEntitiesEloquentBuilder(): Builder
    {
        return $this->getModel()->newQuery()
            ->whereIn('type', [
                Constants::DRIVER_TYPE_ENTITY,
                Constants::DRIVER_TYPE_ENTITY_CONTENT,
            ])
            ->whereIn('package_id', app('core.packages')->getActivePackageIds());
    }

    public function getEntityPackageAlias(): array
    {
        try {
            return Driver::query()
                ->selectRaw('packages.alias, core_drivers.name')
                ->join('packages', 'packages.name', '=', 'core_drivers.package_id')
                ->where('core_drivers.type', Constants::DRIVER_TYPE_ENTITY)
                ->pluck('alias', 'name')
                ->toArray();
        } catch (\Throwable) {
        }

        return [];
    }

    public function getPackageIdByEntityType(string $name): ?string
    {
        $key = sprintf('core::driver::getPackageIdByEntityType(%s)', $name);

        return LoadReduce::get($key, function () use ($name) {
            return Arr::get($this->getEntityPackageIds(), $name);
        });
    }

    public function getEntityPackageIds(): array
    {
        return localCacheStore()->rememberForever(__METHOD__, function () {
            try {
                return Driver::query()
                    ->selectRaw('package_id, name')
                    ->where('core_drivers.type', Constants::DRIVER_TYPE_ENTITY)
                    ->pluck('package_id', 'name')
                    ->toArray();
            } catch (\Throwable) {
            }

            return [];
        });
    }

    public function loadDrivers(
        string $type,
        ?string $resolution = null,
        ?bool $active = true,
        ?string $version = null,
        ?bool $preload = null,
        ?string $packageId = null
    ): array {
        $wheres = [
            'core_drivers.type'  => $type,
            'packages.is_active' => 1,
        ];

        if ($version !== null) {
            $wheres['core_drivers.version'] = $version;
        }

        if ($resolution !== null) {
            $wheres['core_drivers.resolution'] = $resolution;
        }

        if ($active !== null) {
            $wheres['core_drivers.is_active'] = $active ? 1 : 0;
        }

        if ($preload !== null) {
            $wheres['core_drivers.is_preload'] = $preload ? 1 : 0;
        }

        if ($packageId !== null) {
            $wheres['core_drivers.package_id'] = $packageId;
        }

        try {
            // TODO should join to active package.
            $drivers = $this->getModel()->newQuery()
                ->selectRaw('core_drivers.*, packages.alias as package_alias')
                ->join('packages', 'packages.name', '=', 'core_drivers.package_id')
                ->where($wheres)
                ->orderBy('name')
                ->get(['name', 'version', 'driver', 'package_id', 'alias']);

            /*
             * @warning When adding new method arguments, also support to pass these arguments to event parameters
             */
            app('events')->dispatch('core.driver.override_load_drivers', [&$drivers, $type, $resolution, $active, $version, $preload, $packageId]);

            return $drivers
                ->map(fn ($model) => [
                    $model->name,
                    $model->driver,
                    $model->version,
                    $model->package_alias,
                    $model->package_id,
                    $model->alias,
                ])
                ->toArray();
        } catch (\Throwable $throwable) {
            Log::error('load drivers error message: ' . $throwable->getMessage());
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function loadDriver(string $type, string $name, ?string $resolution = null): array
    {
        $where = [
            ['type', '=', $type],
            ['name', '=', str_replace('-', '_', $name)],
        ];

        if (null !== $resolution) {
            $where[] = ['resolution', '=', $resolution];
        }

        /** @var ?Driver $resource */
        $resource = $this->getModel()->newQuery()->firstWhere($where);

        if (!$resource) {
            throw new \InvalidArgumentException("Could not find driver '$type' and '$name' ");
        }

        try {
            /*
             * @warning When adding new method arguments, also support to pass these arguments to event parameters
             */
            app('events')->dispatch('core.driver.override_load_driver', [$resource, $type, $name, $resolution]);
        } catch (\Throwable $throwable) {
            Log::error('load driver error message: ' . $throwable->getMessage());
        }

        return [
            $resource->name,
            $resource->driver,
            $resource->version,
            $resource->package_id,
        ];
    }

    private function loadModelMorphedMap(): array
    {
        return localCacheStore()->rememberForever(__METHOD__, function () {
            $result = [];
            $rows   = $this->getModel()->newQuery()->whereIn('type', ['entity', 'entity-content'])->get();

            foreach ($rows as $row) {
                $result[$row->name] = $row->driver;
            }

            return $result;
        });
    }

    public function bootingKernelConfigs(): void
    {
        Relation::morphMap($this->loadModelMorphedMap());
    }

    /**
     * @inheritDoc
     */
    public function loadEntityModuleMap(): array
    {
        return localCacheStore()->rememberForever(
            __METHOD__,
            fn () => $this->getModel()
                ->newModelQuery()
                ->whereIn('type', [
                    Constants::DRIVER_TYPE_ENTITY,
                    Constants::DRIVER_TYPE_ENTITY_CONTENT,
                ])
                ->get(['name', 'module_id'])
                ->collect()
                ->pluck('module_id', 'name')
                ->toArray()
        );
    }
}
